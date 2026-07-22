<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as Controller;
use Modules\User\Entities\Permission;
use Modules\User\Services\PermissionService;
use Modules\User\Transformers\PermissionResource;

class PermissionsController extends Controller
{

    public function index(Request $request, PermissionService $permissionService)
    {
        //set permission
        $permission = $permissionService->index($request);
        if (!$permission->success) {
            return sendError($permission->message, '', '404');
        }

        return sendResponse(new PermissionResource($permission->data), 'Permission register successfully.');
    }

    public function store(Request $request, PermissionService $permissionService)
    {
        //set permission
        $permission = $permissionService->store($request);
        if (!$permission->success) {
            return sendError($permission->message, '', '404');
        }

        return sendResponse(new PermissionResource($permission->data), 'Permission register successfully.');
    }

    public function show($id)
    {
        $permission = Permission::find($id);

        if (is_null($permission)) {
            return sendError('Permission not found.');
        }

        return sendResponse(new PermissionResource($permission), 'Permission retrieved successfully.');
    }

    public function update(Request $request, PermissionService $permissionService)
    {
        //set permission
        $permission = $permissionService->update($request);
        if (!$permission->success) {
            return sendError($permission->message, '', '404');
        }

        return sendResponse(new PermissionResource($permission->data), 'Update Permission Profile successfully.');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        return sendResponse([], 'Permission deleted successfully.');
    }
}
