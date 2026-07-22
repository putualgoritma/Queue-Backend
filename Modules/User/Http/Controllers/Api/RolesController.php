<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as Controller;
use Modules\User\Entities\Role;
use Modules\User\Services\RoleService;
use Modules\User\Transformers\RoleResource;

class RolesController extends Controller
{

    public function index(Request $request, RoleService $roleService)
    {
        //set role
        $role = $roleService->index($request);
        if (!$role->success) {
            return sendError($role->message, '', '404');
        }

        return sendResponse(new RoleResource($role->data), 'Role register successfully.');
    }

    public function store(Request $request, RoleService $roleService)
    {
        //set role
        $role = $roleService->store($request);
        if (!$role->success) {
            return sendError($role->message, '', '404');
        }

        return sendResponse(new RoleResource($role->data), 'Role register successfully.');
    }

    public function show($id)
    {
        $role = Role::find($id);

        if (is_null($role)) {
            return sendError('Role not found.');
        }

        return sendResponse(new RoleResource($role), 'Role retrieved successfully.');
    }

    public function update(Request $request, RoleService $roleService)
    {
        //set role
        $role = $roleService->update($request);
        if (!$role->success) {
            return sendError($role->message, '', '404');
        }

        return sendResponse(new RoleResource($role->data), 'Role update successfully.');
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return sendResponse([], 'Role deleted successfully.');
    }
}
