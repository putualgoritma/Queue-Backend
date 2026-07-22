<?php

namespace Modules\User\Services;

use Illuminate\Http\Request;
use Modules\User\Entities\Approval as Modal;

class ApprovalService
{
    public $createRequest;
    public $updateRequest;

    public function __construct()
    {
        
    }
    
    public function index(Request $request)
    {
        if (isset($request->page)) {
            $rows = Modal::select('*')->FilterInput($request)->SetOrderBy($request)
                ->paginate($request->per_page, ['*'], 'page', $request->page);
        } else {
            $rows = Modal::select('*')->FilterInput($request)->SetOrderBy($request)->get();
        }

        return sendResponse($rows, 'Data index successfully', 'plain');
    }

    public function patch(Request $request)
    {
        //if row not exist
        if (!($row = Modal::where('id', $request->id)->exists())) {
            return sendError('Data does not exist!', '', '404', 'plain');
        }
        $row = Modal::find($request->id);
        $data = array_merge($request->all());
        $row->fill($data)->save();
        return sendResponse($row, 'Data Patch successfully', 'plain');
    }
    
    public function store(Request $request)
    {        
        //find login id
        $authUser = auth('api')->user();
        $login_id = $authUser->id;
        $request->request->add(['login_id' => $login_id]);
        //validate input
        $data = array_merge($request->all());
        //store data
        $row = Modal::create($data);
        return sendResponse($row, 'Data store successfully', 'plain');
    }

    public function update(Request $request)
    {
        //if row not exist
        if (!($row = Modal::where('id', $request->id)->exists())) {
            return sendError('Data does not exist!', '', '404', 'plain');
        }
        //validate input
        $data = array_merge($request->all());
        return sendResponse($row, 'Data update successfully', 'plain');
    }
}
