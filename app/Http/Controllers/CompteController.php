<?php

namespace App\Http\Controllers;

use App\Models\Compte;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Accounts",
 *     description="Account management operations"
 * )
 */
class CompteController extends Controller
{
    use ApiResponseTrait;

    /**
     * @OA\Get(
     *     path="/monteiro.daisa/v1/comptes",
     *     summary="List all accounts",
     *     tags={"Accounts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Accounts retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Comptes retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $comptes = Compte::with('client')->get();

        // Formater les données comptes
        $formattedData = $comptes->map(function ($compte) {
            return $this->formatCompteData($compte);
        });

        return $this->successResponse($formattedData, 'Comptes retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/monteiro.daisa/v1/comptes",
     *     summary="Create a new account",
     *     tags={"Accounts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"client_id","type","devise"},
     *             @OA\Property(property="client_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *             @OA\Property(property="type", type="string", enum={"courant","epargne"}, example="courant"),
     *             @OA\Property(property="devise", type="string", example="XOF"),
     *             @OA\Property(property="titulaire", type="string", example="John Doe")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Account created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Account created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Client already has an account",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Client already has an account")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        // Vérifier que le client n'a pas déjà de compte
        $existingCompte = Compte::where('client_id', $request->client_id)->first();
        if ($existingCompte) {
            return $this->errorResponse('Client already has an account', 400);
        }

        $compte = Compte::create($request->all());

        $formattedData = $this->formatCompteData($compte);

        return $this->successResponse($formattedData, 'Account created successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/monteiro.daisa/v1/comptes/{compte}",
     *     summary="Get a specific account",
     *     tags={"Accounts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="compte",
     *         in="path",
     *         required=true,
     *         description="Account UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Account retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Account retrieved successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function show(Compte $compte)
    {
        $compte->load('client');
        $formattedData = $this->formatCompteData($compte);

        return $this->successResponse($formattedData, 'Account retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/monteiro.daisa/v1/comptes/{compte}",
     *     summary="Update an account",
     *     tags={"Accounts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="compte",
     *         in="path",
     *         required=true,
     *         description="Account UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="type", type="string", enum={"courant","epargne"}, example="courant"),
     *             @OA\Property(property="devise", type="string", example="XOF"),
     *             @OA\Property(property="titulaire", type="string", example="John Doe"),
     *             @OA\Property(property="statut", type="string", enum={"actif","bloque","ferme"}, example="actif")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Account updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Account updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function update(Request $request, Compte $compte)
    {
        $compte->update($request->all());

        $formattedData = $this->formatCompteData($compte);

        return $this->successResponse($formattedData, 'Account updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/monteiro.daisa/v1/comptes/{compte}",
     *     summary="Delete an account",
     *     tags={"Accounts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="compte",
     *         in="path",
     *         required=true,
     *         description="Account UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Account deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Account deleted successfully")
     *         )
     *     )
     * )
     */
    public function destroy(Compte $compte)
    {
        $compte->delete();

        return $this->successResponse(null, 'Account deleted successfully');
    }
}