<?php

namespace Modules\User\Services;

use Illuminate\Http\Request;
use Modules\User\Entities\Log as Model;

class LogService
{
    public $createRequest;
    public $updateRequest;

    public function __construct()
    {
        
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
        //if row not exist
        if (!($row = Model::where('id', $request->id)->exists())) {
            return sendError('Data does not exist!', '', '404', 'plain');
        }
        //validate input
        $data = array_merge($request->all());
        return sendResponse($row, 'Data update successfully', 'plain');
    }
}
