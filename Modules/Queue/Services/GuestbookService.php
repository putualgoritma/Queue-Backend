<?php

namespace Modules\Queue\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Queue\Entities\Guestbook as Model;
use Modules\Queue\Entities\Service;
use Modules\Queue\Http\Requests\GuestbookCreateRequest;
use Modules\Queue\Http\Requests\GuestbookUpdateRequest;
use Validator;

class GuestbookService
{

    private $createRequest;
    private $updateRequest;

    public function __construct()
    {
        $this->createRequest = new GuestbookCreateRequest();
        $this->updateRequest = new GuestbookUpdateRequest();
    }

    public function exist(Request $request)
    {
        if (!(Model::FilterInput($request)->exists())) {
            return false;
        } else {
            return true;
        }
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
            if (isset($request->contact_address)) {
                //set address
                foreach ($request->contact_address as $key => $address) {
                    if (!empty($address)) {
                        $dataAddress = array_merge($address, ['contact_id' => $row->id]);
                        $row->attach;
                        $row->attach($row->id, $dataAddress);
                        //$rowAddress = ContactAddress::create($dataAddress);
                    }
                }
            }
            return sendResponse($row, 'Data store successfully', 'plain');
        }
    }

    public function update(Request $request)
    {
        //if data not exist
        if (!(Model::where('id', $request->id)->exists())) {
            return sendError('Data does not exist', '', '404', 'plain');
        }
        //validate input
        $data = array_merge($request->all());
        $validator = Validator::make($data, $this->updateRequest->rules(), $this->updateRequest->messages());
        if ($validator->fails()) {
            return sendError('Data update error: ' . $validator->errors()->all()[0], '', '404', 'plain');
        } else {
            $row = Model::find($request->id);
            $row->fill($data)->save();
            if (isset($request->contact_address) && !empty($request->contact_address)) {
                //reset address
                //ContactAddress::where('contact_id', $row->id)->delete();
                $row->dettach($row->id);
                //set address
                foreach ($request->contact_address as $key => $address) {
                    if (!empty($address)) {
                        $dataAddress = array_merge($address, ['contact_id' => $row->id]);
                        $row->contactAddress()->attach($row->id, $dataAddress);
                        //$rowAddress = ContactAddress::create($dataAddress);
                    }
                }
            }
            return sendResponse($row, 'Data update successfully', 'plain');
        }
    } 
}
