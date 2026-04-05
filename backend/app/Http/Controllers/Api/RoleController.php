<?php

namespace App\Http\Controllers\Api;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = Role::with('permissions')->get();
        return response()->json($roles);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:roles,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'integer|exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $role = Role::create($request->only(['name', 'display_name', 'description']));

        if ($request->has('permission_ids')) {
            $role->permissions()->sync($request->permission_ids);
        }

        $role->load('permissions');

        return response()->json($role, 201);
    }

    public function show(int $id): JsonResponse
    {
        $role = Role::with('permissions')->findOrFail($id);
        return response()->json($role);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        if ($role->name === 'owner') {
            return response()->json(['message' => 'Cargo owner não pode ser modificado'], 403);
        }

        $validator = Validator::make($request->all(), [
            'display_name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'integer|exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $role->update($request->only(['display_name', 'description']));

        if ($request->has('permission_ids')) {
            $role->permissions()->sync($request->permission_ids);
        }

        $role->load('permissions');

        return response()->json($role);
    }

    public function destroy(int $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        if ($role->name === 'owner') {
            return response()->json(['message' => 'Cargo owner não pode ser deletado'], 403);
        }

        if ($role->users()->count() > 0) {
            return response()->json(['message' => 'Cargo possui usuários associados'], 422);
        }

        $role->permissions()->detach();
        $role->delete();

        return response()->json(['message' => 'Cargo deletado com sucesso']);
    }

    public function permissions(): JsonResponse
    {
        $permissions = Permission::all();
        return response()->json($permissions);
    }

    public function assignUser(Request $request, int $roleId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $role = Role::findOrFail($roleId);
        $role->users()->syncWithoutDetaching([$request->user_id]);

        return response()->json(['message' => 'Usuário atribuído ao cargo com sucesso']);
    }

    public function removeUser(int $roleId, int $userId): JsonResponse
    {
        $role = Role::findOrFail($roleId);
        $role->users()->detach($userId);

        return response()->json(['message' => 'Usuário removido do cargo com sucesso']);
    }
}