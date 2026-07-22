<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as Controller;
use Modules\User\Entities\User;
use Modules\User\Services\UserService;
use Modules\User\Transformers\UserResource;
use Modules\User\Services\ContactService;
use Modules\User\Entities\Contact;

class UsersController extends Controller
{

    public function test()
    {
        $contactService = new ContactService();
        $requestExist = setRequest(['ty' => '1']);
        if (!($contactService->exist($requestExist))) {
            $outVal = 0;
        }else{
            $outVal = 1;
        }
        return $outVal;
    }

    public function login(Request $request, UserService $userService)
    {
        //set user
        $user = $userService->login($request);
        if (!$user->success) {
            return sendError($user->message, '', '404');
        }

        return sendResponse($user->data, 'User login successfully.');
    }

    public function index(Request $request, UserService $userService)
    {
        //set user
        $user = $userService->index($request);
        if (!$user->success) {
            return sendError($user->message, '', '404');
        }

        return sendResponse($user->data, 'User list successfully.');
    }

    public function store(Request $request, UserService $userService)
    {
        //set user
        $user = $userService->store($request);
        if (!$user->success) {
            return sendError($user->message, '', '404');
        }

        return sendResponse(new UserResource($user->data), 'User register successfully.');
    }

    public function register(Request $request, UserService $userService)
    {
        //set userContact
        $userContact = $userService->storeContact($request);
        if (!$userContact->success) {
            return sendError($userContact->message, '', '404');
        }
        
        return sendResponse(new UserResource($userContact->data), 'User contact successfully.');
    }

    public function activate(Request $request, UserService $userService)
    {
        //set user
        $user = $userService->patch($request);
        if (!$user->success) {
            return sendError($user->message, '', '404');
        }

        return sendResponse($user->data, 'Status approupdateved successfully.');
    }

    public function registered($id, UserService $userService)
    {
        //set user
        $user = $userService->registered($id, 'registered', 'register');
        if (!$user->success) {
            return sendError($user->message, '', '404');
        }

        return sendResponse(new UserResource($user->data), 'Register approved successfully.');
    }

    public function activated($id, UserService $userService)
    {
        //set user
        $user = $userService->activated($id, 'activated', 'activate');
        if (!$user->success) {
            return sendError($user->message, '', '404');
        }

        return sendResponse(new UserResource($user->data), 'Register approved successfully.');
    }

    public function updateProfile(Request $request, UserService $userService)
    {
        //set userContact
        $userContact = $userService->storeContact($request);
        if (!$userContact->success) {
            return sendError($userContact->message, '', '404');
        }

        return sendResponse(new UserResource($userContact->data), 'Update User Profile successfully.');
    }

    public function show($id)
    {
        $user = User::find($id);

        if (is_null($user)) {
            return sendError('User not found.');
        }

        return sendResponse(new UserResource($user), 'User retrieved successfully.');
    }

    public function update(Request $request, UserService $userService)
    {
    //set userContact
        $userContact = $userService->update($request);
        if (!$userContact->success) {
            return sendError($userContact->message, '', '404');
        }

        return sendResponse(new UserResource($userContact->data), 'Update User successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return sendResponse([], 'User deleted successfully.');
    }
}
