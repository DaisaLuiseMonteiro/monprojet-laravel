<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Http\Requests\ClientRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Info(
 *     title="Banking API",
 *     version="1.0.0",
 *     description="API for banking operations including clients and accounts management"
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class ClientController extends Controller
{
    use ApiResponseTrait;

    /**
     * @OA\Get(
     *     path="/monteiro.daisa/v1/clients",
     *     summary="List all clients",
     *     tags={"Clients"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for name, first name or phone",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Clients retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Clients retrieved successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Client::with('user');

        // Apply filters if needed
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%");
        }

        $clients = $query->paginate($request->get('limit', 10));

        return $this->successResponse($clients, 'Clients retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/monteiro.daisa/v1/clients",
     *     summary="Create a new client",
     *     tags={"Clients"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nom","prenom","email","password","telephone","cni","sexe"},
     *             @OA\Property(property="nom", type="string", example="Doe"),
     *             @OA\Property(property="prenom", type="string", example="John"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@gmail.com"),
     *             @OA\Property(property="password", type="string", minLength=6, example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123"),
     *             @OA\Property(property="telephone", type="string", pattern="^(70|75|76|77|78)\\d{7}$", example="701234567"),
     *             @OA\Property(property="cni", type="string", pattern="^[12]\\d{12}$", example="1234567890123"),
     *             @OA\Property(property="sexe", type="string", enum={"M","F"}, example="M"),
     *             @OA\Property(property="adresse", type="string", example="123 Main St")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Client created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Client created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(ClientRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id(); // Assuming client is created by authenticated user

        $client = Client::create($data);

        return $this->successResponse($client, 'Client created successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/monteiro.daisa/v1/clients/{client}",
     *     summary="Get a specific client",
     *     tags={"Clients"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="client",
     *         in="path",
     *         required=true,
     *         description="Client UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Client retrieved successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Client not found")
     *         )
     *     )
     * )
     */
    public function show(Client $client)
    {
        $client->load('user', 'comptes');

        return $this->successResponse($client, 'Client retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/monteiro.daisa/v1/clients/{client}",
     *     summary="Update a client",
     *     tags={"Clients"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="client",
     *         in="path",
     *         required=true,
     *         description="Client UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="nom", type="string", example="Doe"),
     *             @OA\Property(property="prenom", type="string", example="John"),
     *             @OA\Property(property="telephone", type="string", pattern="^(70|75|76|77|78)\\d{7}$", example="701234567"),
     *             @OA\Property(property="sexe", type="string", enum={"M","F"}, example="M"),
     *             @OA\Property(property="adresse", type="string", example="123 Main St")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Client updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function update(ClientRequest $request, Client $client)
    {
        $client->update($request->validated());

        return $this->successResponse($client, 'Client updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/monteiro.daisa/v1/clients/{client}",
     *     summary="Delete a client",
     *     tags={"Clients"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="client",
     *         in="path",
     *         required=true,
     *         description="Client UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Client deleted successfully")
     *         )
     *     )
     * )
     */
    public function destroy(Client $client)
    {
        $client->delete();

        return $this->successResponse(null, 'Client deleted successfully');
    }
}