<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as Controller;
use Modules\User\Services\UserService;
use Modules\User\Services\UserStaffService;

class UserStaffController extends Controller
{

   public function index(Request $request, UserStaffService $service)
    {
        //set user
        $user = $service->index($request);
        if (!$user->success) {
            return sendError($user->message, '', '404');
        }

        return sendResponse($user->data, 'User list successfully.');
    }

    public function store(Request $request, UserService $userService, UserStaffService $service)
    {
        //set user
        $user = $userService->storeContact($request);
        if (!$user->success) {
            return sendError($user->message, '', '404');
        }else{
            $requestData = setRequest(['user_id' => $user->data->id, "status" => $request->user_staff_status, "depertement_id" => $request->depertement_id, "job_id" => $request->job_id, "counter_id" => $request->counter_id]);
            $userStaff = $service->store($requestData);
            if (!$userStaff->success) {
                return sendError($userStaff->message, '', '404');
            }
        }

        return sendResponse($user->data, 'User register successfully.');
    }

    public function show($id)
    {
        $user = User::find($id);

        if (is_null($user)) {
            return sendError('User not found.');
        }

        return sendResponse($user, 'User retrieved successfully.');
    }

    public function update(Request $request, UserService $userService, UserStaffService $service)
    {
    //set userContact
        $userContact = $userService->storeContact($request);
        if (!$userContact->success) {
            return sendError($userContact->message, '', '404');
        }else{
            $requestData = setRequest(['user_id' => $userContact->data->id, "status" => $request->user_staff_status, "depertement_id" => $request->depertement_id, "job_id" => $request->job_id, "counter_id" => $request->counter_id]);
            $userStaff = $service->update($requestData);
            if (!$userStaff->success) {
                return sendError($userStaff->message, '', '404');
            }
        }

        return sendResponse($userContact->data, 'Update User successfully.');
    }

    public function destroy(Request $request, UserService $userService)
    {
        $user->delete();

        return sendResponse([], 'User deleted successfully.');
    }
}
