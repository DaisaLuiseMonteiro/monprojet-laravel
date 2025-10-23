<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait ApiResponseTrait
{
    /**
     * Formatte les données de requête entrante selon une structure définie
     *
     * @param Request $request
     * @param array $fields
     * @return array
     */
    protected function formatRequestData(Request $request, array $fields = []): array
    {
        $data = [];

        foreach ($fields as $field => $config) {
            if (is_string($config)) {
                // Configuration simple : 'field_name'
                $data[$field] = $request->input($config);
            } elseif (is_array($config)) {
                // Configuration avancée
                $inputKey = $config['key'] ?? $field;
                $default = $config['default'] ?? null;
                $transform = $config['transform'] ?? null;

                $value = $request->input($inputKey, $default);

                if ($transform && is_callable($transform)) {
                    $value = $transform($value);
                }

                $data[$field] = $value;
            }
        }

        return $data;
    }

    /**
     * Formatte les données de réponse sortante selon une structure définie
     *
     * @param mixed $data
     * @param array $structure
     * @param array $includes
     * @return array
     */
    protected function formatResponseData($data, array $structure = [], array $includes = []): array
    {
        if (is_null($data)) {
            return [];
        }

        if (is_array($data) || $data instanceof \Illuminate\Support\Collection) {
            return collect($data)->map(function ($item) use ($structure, $includes) {
                return $this->formatSingleItem($item, $structure, $includes);
            })->toArray();
        }

        return $this->formatSingleItem($data, $structure, $includes);
    }

    /**
     * Formatte un élément unique
     *
     * @param mixed $item
     * @param array $structure
     * @param array $includes
     * @return array
     */
    private function formatSingleItem($item, array $structure, array $includes): array
    {
        $formatted = [];

        // Appliquer la structure de base
        foreach ($structure as $key => $config) {
            if (is_string($config)) {
                // Accès direct à l'attribut
                $formatted[$key] = $this->getNestedValue($item, $config);
            } elseif (is_array($config)) {
                // Configuration avancée
                $source = $config['source'] ?? $key;
                $default = $config['default'] ?? null;
                $transform = $config['transform'] ?? null;

                $value = $this->getNestedValue($item, $source, $default);

                if ($transform && is_callable($transform)) {
                    $value = $transform($value, $item);
                }

                $formatted[$key] = $value;
            }
        }

        // Ajouter les inclusions
        foreach ($includes as $include => $includeConfig) {
            if (is_string($includeConfig)) {
                $formatted[$include] = $this->getNestedValue($item, $includeConfig);
            } elseif (is_array($includeConfig)) {
                $relation = $includeConfig['relation'] ?? $include;
                $structure = $includeConfig['structure'] ?? [];
                $includes_nested = $includeConfig['includes'] ?? [];

                $relatedData = $this->getNestedValue($item, $relation);
                $formatted[$include] = $this->formatResponseData($relatedData, $structure, $includes_nested);
            }
        }

        return $formatted;
    }

    /**
     * Récupère une valeur imbriquée dans un objet ou tableau
     *
     * @param mixed $data
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    private function getNestedValue($data, string $key, $default = null)
    {
        if (is_null($data)) {
            return $default;
        }

        // Support pour les clés imbriquées avec point
        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);
            $value = $data;

            foreach ($keys as $k) {
                if (is_array($value) && isset($value[$k])) {
                    $value = $value[$k];
                } elseif (is_object($value) && (isset($value->$k) || method_exists($value, $k))) {
                    $value = is_callable([$value, $k]) ? $value->$k() : $value->$k;
                } else {
                    return $default;
                }
            }

            return $value;
        }

        // Accès direct
        if (is_array($data) && isset($data[$key])) {
            return $data[$key];
        }

        if (is_object($data)) {
            if (isset($data->$key)) {
                return $data->$key;
            }

            if (method_exists($data, $key)) {
                return $data->$key();
            }
        }

        return $default;
    }

    /**
     * Format standard pour les réponses de succès
     */
    protected function successResponse($data, string $message = '', int $status = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'data' => $data,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        return response()->json($response, $status);
    }

    /**
     * Format standard pour les réponses d'erreur
     */
    protected function errorResponse(string $message, int $status = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Formater les données compte pour la réponse
     */
    protected function formatCompteData($compte)
    {
        return [
            'id' => $compte->id,
            'numeroCompte' => $compte->numeroCompte,
            'titulaire' => $compte->titulaire,
            'type' => $compte->type,
            'solde' => $compte->getSolde(),
            'devise' => $compte->devise,
            'dateCreation' => $compte->dateCreation?->toISOString(),
            'statut' => $compte->statut,
            'motifBlocage' => $compte->metadata['motifBlocage'] ?? null,
            'metadata' => [
                'derniereModification' => $compte->updated_at?->toISOString(),
                'version' => $compte->metadata['version'] ?? 1,
            ],
        ];
    }
}