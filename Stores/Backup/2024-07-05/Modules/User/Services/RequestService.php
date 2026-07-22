<?php

namespace Modules\User\Services;

use Illuminate\Http\Request;
use Modules\User\Entities\Request as Model;

class RequestService
{
    public $createRequest;
    public $updateRequest;

    public function __construct()
    {
        
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

    public function patch(Request $request)
    {
        //if request not exist
        if (!($row = Model::where('id', $request->id)->exists())) {
            return sendError('Data does not exist!', '', '404', 'plain');
        }
        $row = Model::find($request->id);
        $data = array_merge($request->all());
        $row->fill($data)->save();
        return sendResponse($row, 'Data Patch successfully', 'plain');
    }
    
    public function store(Request $request)
    {
        //validate input
        $data = array_merge($request->all());
        $row = Model::create($data);
        return sendResponse($row, 'Data store successfully', 'plain');
    }

    public function update(Request $request)
    {
        //if request not exist
        if (!($request = Model::where('id', $request->id)->exists())) {
            return sendError('Data does not exist!', '', '404', 'plain');
        }
        //validate input
        $data = array_merge($request->all());
        return sendResponse($request, 'Data update successfully', 'plain');
    }
}
