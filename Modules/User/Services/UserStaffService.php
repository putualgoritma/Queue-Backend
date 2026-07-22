<?php

namespace Modules\User\Services;

use Illuminate\Http\Request;
use Modules\User\Entities\UserStaff as Model;
use Modules\User\Entities\User;
use Modules\User\Http\Requests\UserStaffCreateRequest;
use Modules\User\Http\Requests\UserStaffUpdateRequest;
use Validator;

class UserStaffService
{

    private $contactService;
    private $createRequest;
    private $updateRequest;

    public function __construct()
    {
        $this->createRequest = new UserStaffCreateRequest();
        $this->updateRequest = new UserStaffUpdateRequest();
    }

    public function index(Request $request)
    {
        if (isset($request->page)) {
            $rows = User::select('*')->FilterInput($request)->SetOrderBy($request)->with('contact')->with('userStaff')
                ->paginate($request->per_page, ['*'], 'page', $request->page);
        } else {
            $rows = User::select('*')->FilterInput($request)->SetOrderBy($request)->with('contact')->with('userStaff')->get();
        }

        return sendResponse($rows, 'Data index successfully', 'plain');
    }

    public function store(Request $request)
    {
        //validate input
        $data = array_merge($request->all());
        $validator = Validator::make($data, $this->createRequest->rules(), $this->createRequest->messages());
        if ($validator->fails()) {
            return sendError('Data store error: ' . $validator->errors()->all()[0], '', '404', 'plain');
        } else {
            $row = Model::create($data);
            $row = $row->refresh();
            return sendResponse($row, 'Data store successfully', 'plain');
        }
    }

    public function update(Request $request)
    {
        //if data not exist
        if (!($row = Model::where('user_id', $request->user_id)->exists())) {
            $row = $this->store($request);
            if (!$row->success) {
                return sendError($row->message, '', '404', 'plain');
            }
        }
        //validate input
        $data = array_merge($request->all());
        $validator = Validator::make($data, $this->updateRequest->rules($data['user_id']), $this->updateRequest->messages());
        if ($validator->fails()) {
            return sendError('Data update error: ' . $validator->errors()->all()[0], '', '404', 'plain');
        } else {
            $row = Model::where('user_id', $request->user_id)->first();
            $row->fill($data)->save();
            return sendResponse($row, 'Data update successfully', 'plain');
        }
    }
}
