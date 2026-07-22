<?php

namespace Modules\User\Services;

use Illuminate\Http\Request;
use Modules\User\Entities\Permission as Model;
use Modules\User\Http\Requests\PermissionCreateRequest;
use Modules\User\Http\Requests\PermissionUpdateRequest;
use Validator;

class PermissionService
{
    public $createRequest;
    public $updateRequest;

    public function __construct()
    {
        $this->createRequest = new PermissionCreateRequest();
        $this->updateRequest = new PermissionUpdateRequest();
    }

    public function index(Request $request)
    {
        if (isset($request->page)) {
            $rows = Model::select('*')->FilterInput($request)->SetOrderBy($request)
                ->paginate($request->per_page, ['*'], 'page', $request->page);
        } else {
            $rows = Model::select('*')->FilterInput($request)->SetOrderBy($request)->get();
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
        if (!($row = Model::where('id', $request->id)->exists())) {
            return sendError('Data does not exist!', '', '404', 'plain');
        }
        //validate input
        $data = array_merge($request->all());
        $validator = Validator::make($data, $this->updateRequest->rules($data['id']), $this->updateRequest->messages());
        if ($validator->fails()) {
            return sendError('Data update error: ' . $validator->errors()->all()[0], '', '404', 'plain');
        } else {
            $row = Model::find($request->id);
            $row->fill($data)->save();
            return sendResponse($row, 'Data update successfully', 'plain');
        }
    }
}
