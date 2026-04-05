<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::with('roles');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->paginate(20);

        return response()->json($users);
    }

    public function show(int $id): JsonResponse
    {
        $user = User::with(['roles', 'roles.permissions'])->findOrFail($id);
        return response()->json($user);
    }

    public function updateRole(Request $request, int $id): JsonResponse
    {
        $currentUser = $request->user();
        
        if ($currentUser->role?->name !== 'owner') {
            return response()->json(['message' => 'Apenas o Dono pode alterar cargos'], 403);
        }

        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'role_id' => 'required|integer|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $role = Role::findOrFail($request->role_id);
        
        $user->roles()->sync([$role->id]);

        $user->load('roles');

        return response()->json($user);
    }

    public function assignRole(Request $request): JsonResponse
    {
        $currentUser = $request->user();
        
        if ($currentUser->role?->name !== 'owner') {
            return response()->json(['message' => 'Apenas o Dono pode atribuir cargos'], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'role_id' => 'required|integer|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::findOrFail($request->user_id);
        $role = Role::findOrFail($request->role_id);

        $user->roles()->sync([$role->id]);

        $user->load('roles');

        return response()->json([
            'message' => 'Cargo atribuído com sucesso',
            'user' => $user
        ]);
    }

    public function listRoles(): JsonResponse
    {
        $roles = Role::with('permissions')->get();
        return response()->json($roles);
    }
}