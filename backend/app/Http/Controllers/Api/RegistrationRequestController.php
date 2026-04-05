<?php

namespace App\Http\Controllers\Api;

use App\Models\RegistrationRequest;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class RegistrationRequestController extends Controller
{
    public function index(): JsonResponse
    {
        $requests = RegistrationRequest::with(['requestedBy', 'approvedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($requests);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users|unique:registration_requests',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $registrationRequest = RegistrationRequest::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'status' => 'pending',
            'requested_by' => $request->user()?->id,
        ]);

        return response()->json([
            'message' => 'Solicitação de cadastro enviada. Aguarde aprovação.',
            'request' => $registrationRequest,
        ], 201);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $currentUser = $request->user();
        
        if ($currentUser->role?->name !== 'owner') {
            return response()->json(['message' => 'Apenas o Dono pode aprovar cadastros'], 403);
        }

        $registrationRequest = RegistrationRequest::findOrFail($id);

        if ($registrationRequest->status !== 'pending') {
            return response()->json(['message' => 'Esta solicitação já foi processada'], 400);
        }

        $user = User::create([
            'name' => $registrationRequest->name,
            'email' => $registrationRequest->email,
            'password' => $registrationRequest->password,
        ]);

        $roleId = $request->input('role_id');
        
        if ($roleId) {
            $role = Role::findOrFail($roleId);
            $user->roles()->sync([$role->id]);
        }

        $registrationRequest->update([
            'status' => 'approved',
            'approved_by' => $currentUser->id,
            'approved_at' => now(),
        ]);

        return response()->json([
            'message' => 'Usuário criado com sucesso',
            'user' => $user->load('roles'),
        ]);
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $currentUser = $request->user();
        
        if ($currentUser->role?->name !== 'owner') {
            return response()->json(['message' => 'Apenas o Dono pode rejeitar cadastros'], 403);
        }

        $registrationRequest = RegistrationRequest::findOrFail($id);

        if ($registrationRequest->status !== 'pending') {
            return response()->json(['message' => 'Esta solicitação já foi processada'], 400);
        }

        $registrationRequest->update([
            'status' => 'rejected',
            'approved_by' => $currentUser->id,
            'approved_at' => now(),
        ]);

        return response()->json(['message' => 'Solicitação rejeitada']);
    }
}