<?php

namespace App\Http\Controllers\Api\Rbac;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Permission::query()->orderBy('group')->orderBy('key')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:150', 'unique:permissions,key'],
            'group' => ['nullable', 'string', 'max:100'],
        ]);

        $permission = Permission::create($data);

        return response()->json([
            'message' => 'Permission created',
            'data' => $permission,
        ], 201);
    }

    public function update(Request $request, Permission $permission)
    {
        $data = $request->validate([
            'key' => [
                'required', 'string', 'max:150',
                Rule::unique('permissions', 'key')->ignore($permission->id),
            ],
            'group' => ['nullable', 'string', 'max:100'],
        ]);

        $permission->update($data);

        return response()->json([
            'message' => 'Permission updated',
            'data' => $permission,
        ]);
    }

    public function destroy(Permission $permission)
    {
        // هيمسح علاقاته تلقائيًا لو FK cascade موجود (عندك)
        $permission->delete();

        return response()->json([
            'message' => 'Permission deleted',
        ]);
    }
}
