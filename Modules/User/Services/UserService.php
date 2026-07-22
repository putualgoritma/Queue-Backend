<?php

namespace Modules\User\Services;

use Auth;
use Illuminate\Http\Request;
use Modules\User\Entities\User as Model;
use Modules\User\Http\Requests\UserCreateRequest;
use Modules\User\Http\Requests\UserUpdateRequest;
use Modules\User\Services\ContactService;
use Validator;

class UserService
{

    private $contactService;
    private $createRequest;
    private $updateRequest;

    public function __construct()
    {
        $this->contactService = new ContactService();
        $this->createRequest = new UserCreateRequest();
        $this->updateRequest = new UserUpdateRequest();
    }

    public function patch(Request $request)
    {
        //if row not exist
        if (!($row = Model::where('id', $request->id)->exists())) {
            return sendError('Data does not exist!', '', '404', 'plain');
        }
        $row = Model::find($request->id);
        $data = array_merge($request->all());
        $row->fill($data)->save();
        return sendResponse($row, 'Data Patch successfully', 'plain');
    }

    public function index(Request $request)
    {
        if (isset($request->page)) {
            $rows = Model::select('*')->FilterInput($request)->SetOrderBy($request)->with('contact')
                ->paginate($request->per_page, ['*'], 'page', $request->page);
        } else {
            $rows = Model::select('*')->FilterInput($request)->SetOrderBy($request)->with('contact')->get();
        }

        return sendResponse($rows, 'Data index successfully', 'plain');
    }

    public function login(Request $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $row = Auth::user();
            $row = Model::where('id', $row->id)->with('role')->with('contact')->with('userStaff')->first();
            $token = $row->createToken('MyApp')->accessToken;

            $permissionsArray = array();
            if (isset($row->role)) {
                foreach ($row->role->permissions as $permissions) {
                    $permissionsArray[] = $permissions->title;
                }
            }
            unset($row->role);

            $response = [
                'user' => $row,
                'token' => $token,
                'permissions' => $permissionsArray,
            ];

            return sendResponse($response, 'Data login successfully.', 'plain');
        } else {
            return sendError('Unauthorised.', ['error' => 'Unauthorised'], '404', 'plain');
        }
    }

    public function registered($id, $status, $statusCheck = '')
    {
        //if data not exist
        if (!($row = Model::where('id', $id)->exists())) {
            return sendError('Data does not exist!', '', '404', 'plain');
        }
        $row = Model::find($id);
        //if data status need check
        if ($statusCheck != '' && $row->status != $statusCheck) {
            return sendError('Data status invalid', '', '404', 'plain');
        }
        $row->status = $status;
        $row->save();
        return sendResponse($row, 'Data Status update successfully', 'plain');
    }

    public function activated($id, $status, $statusCheck = '')
    {
        //if data not exist
        if (!($row = Model::where('id', $id)->exists())) {
            return sendError('Data does not exist!', '', '404', 'plain');
        }
        $row = Model::find($id);
        //if data status need check
        if ($statusCheck != '' && $row->status != $statusCheck) {
            return sendError('Data status invalid', '', '404', 'plain');
        }
        $row->status = $status;
        $row->save();
        return sendResponse($row, 'Data Status update successfully', 'plain');
    }

    public function store(Request $request)
    {
        //check register
        if (!isset($request->register)) {
            $register = date('Y') . '-' . date('m') . '-' . date('d');
            $request->request->add(['register' => $register]);
        }
        //check type
        if (!isset($request->type)) {
            $type = 'member';
            $request->request->add(['type' => $type]);
        }
        //check code
        if (!isset($request->code)) {
            $period = substr($request->register, 0, 4);
            $prefix = config('user.type_prefix')[$request->type];
            $code = $this->codeGenerate($request->type, $prefix, $period);
            $request->request->add(['code' => $code]);
        }
        //validate input
        $data = array_merge($request->all());
        $validator = Validator::make($data, $this->createRequest->rules(), $this->createRequest->messages());
        if ($validator->fails()) {
            return sendError('Data store error: ' . $validator->errors()->all()[0], '', '404', 'plain');
        } else {
            $data['password'] = bcrypt($data['password']);
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
        //check if old type is different
        $row = Model::find($request->id);
        if ($row->type != $request->type) {
            //if different change code
            $period = substr($request->register, 0, 4);
            $prefix = config('user.type_prefix')[$request->type];
            $code = $this->codeGenerate($request->type, $prefix, $period);
            $request->request->add(['code' => $code]);
        }
        //validate input
        $data = array_merge($request->all());
        $validator = Validator::make($data, $this->updateRequest->rules($data['id']), $this->updateRequest->messages());
        if ($validator->fails()) {
            return sendError('Data update error: ' . $validator->errors()->all()[0], '', '404', 'plain');
        } else {
            $row = Model::find($request->id);
            if (isset($data['password'])) {
                $data['password'] = bcrypt($data['password']);
            }
            $row->fill($data)->save();
            return sendResponse($row, 'Data update successfully', 'plain');
        }
    }

    public function storeContact(Request $request)
    {
        //check if data not exist
        $requestUser = setRequest($request->user_data);
        if (!isset($request->user_data['id'])) {
            //create data
            $row = $this->store($requestUser);
            if (!$row->success) {
                return sendError($row->message, '', '404', 'plain');
            }
        } else {
            //update data
            $row = $this->update($requestUser);
            if (!$row->success) {
                return sendError($row->message, '', '404', 'plain');
            }
        }

        //check if contact exist
        if (!empty($request->contact_data)) {
            $requestContact = setRequest($request->contact_data);
            $requestExist = setRequest(['id' => $row->data->contact_id]);
            if (!isset($row->data->contact_id) || !($this->contactService->exist($requestExist))) {
                //create contact
                $contact = $this->contactService->store($requestContact);
                if (!$contact->success) {
                    return sendError($contact->message, '', '404', 'plain');
                }
                $row->data->contact_id = $contact->data->id;
                $row->data->save();
            } else {
                //update contact
                $requestContact = array_merge($request->contact_data, ['id' => $row->data->contact_id]);
                $requestContact = setRequest($requestContact);
                $contact = $this->contactService->update($requestContact);
            }
            if (!$contact->success) {
                return sendError($contact->message, '', '404', 'plain');
            } else {
                return sendResponse($row->data, 'Data Contact store successfully', 'plain');
            }
        } else {
            return sendResponse($row->data, 'Data Contact store successfully', 'plain');
        }
    }

    public function codeGenerate($type, $prefix, $period = '')
    {
        if ($period == '') {
            $period = date('Y');
        }
        $row = Model::where('type', $type)->where('register', 'LIKE', $period . '%')->orderBy('code', 'desc')->first();
        if ($row && (strlen($row->code) == config('user.code_length'))) {
            $last_code = $row->code;
        } else {
            $prefix = $prefix . str_replace("-", "", $period);
            $last_code = acc_codedef_generate($prefix, config('user.code_length'));
        }
        $code = acc_code_generate($last_code, config('user.code_length'), 7);
        return $code;
    }
}
