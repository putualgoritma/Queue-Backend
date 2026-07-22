<?php

namespace Modules\Queue\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Queue\Entities\Queue as Model;
use Modules\Queue\Entities\Service;
use Modules\Queue\Http\Requests\QueueCreateRequest;
use Modules\Queue\Http\Requests\QueueUpdateRequest;
use Validator;

class QueueService
{

    private $createRequest;
    private $updateRequest;

    public function __construct()
    {
        $this->createRequest = new QueueCreateRequest();
        $this->updateRequest = new QueueUpdateRequest();
    }

    public function testAudio(Request $request)
    {
        $this->restAPi('http://localhost:3001/send_welcome?passKey=P1VN3oi4t9j2zKp&loket=');
        return sendResponse('', 'Data store successfully', 'plain');
    }

    public function redisplay(Request $request)
    {
        //remove id
        $request->request->add(['id' => 0]);
        //check register
        if (!isset($request->register)) {
            $request->request->add(['register' => date('Y-m-d')]);
        }
        $request->request->add(['status' => 'active']);
        //get counter 1 active
        $request->request->add(['counter_id' => 1]);
        $rowCounter = $this->activeClose($request);
        $row[0]['antrian'] = $rowCounter->data ? $rowCounter->data->number : 0;
        $row[0]['loket'] = 1;
        $row[0]['queueCode'] = $rowCounter->data ? $rowCounter->data->code : 0;
        //get counter 2 active
        //$request->request->remove('counter_id');
        $request->request->add(['counter_id' => 2]);
        $rowCounter = $this->activeClose($request);
        $row[1]['antrian'] = $rowCounter->data ? $rowCounter->data->number : 0;
        $row[1]['loket'] = 2;
        $row[1]['queueCode'] = $rowCounter->data ? $rowCounter->data->code : 0;
        //get counter 3 active
        //$request->request->remove('counter_id');
        $request->request->add(['counter_id' => 3]);
        $rowCounter = $this->activeClose($request);
        $row[2]['antrian'] = $rowCounter->data ? $rowCounter->data->number : 0;
        $row[2]['loket'] = 3;
        $row[2]['queueCode'] = $rowCounter->data ? $rowCounter->data->code : 0;
        //return row selected
        return sendResponse($row, 'Data store successfully', 'plain');
    }

    public function activeClose(Request $request)
    {
        //check register
        if (!isset($request->register)) {
            $request->request->add(['register' => date('Y-m-d')]);
        }
        $request->request->add(['status' => 'active']);
        $row = Model::selectRaw('queues.*, counters.code as counter_code')
            ->join('services', 'services.id', '=', 'queues.service_id')
            ->join('counters', 'counters.id', '=', 'services.counter_id')
            ->FilterInput($request)->SetOrderBy($request)->with('service')->first();
        //if active is empty check if any call
        if (!$row) {
            //get min number and set as selected
            //$request->request->remove('status');
            $request->request->add(['status' => 'call']);
            $request->request->add(['order_by' => 'number']);
            $request->request->add(['order_by_dir' => 'DESC']);
            $row = Model::selectRaw('queues.*, counters.code as counter_code')
                ->join('services', 'services.id', '=', 'queues.service_id')
                ->join('counters', 'counters.id', '=', 'services.counter_id')
                ->FilterInput($request)->SetOrderBy($request)->with('service')->first(); //first() toSql()
        }
        //if call is empty check if any close
        if (!$row) {
            //get min number and set as selected
            //$request->request->remove('status');
            $request->request->add(['status' => 'close']);
            $request->request->add(['order_by' => 'number']);
            $request->request->add(['order_by_dir' => 'DESC']);
            $row = Model::selectRaw('queues.*, counters.code as counter_code')
                ->join('services', 'services.id', '=', 'queues.service_id')
                ->join('counters', 'counters.id', '=', 'services.counter_id')
                ->FilterInput($request)->SetOrderBy($request)->with('service')->first(); //first() toSql()
        }
        //return row selected
        return sendResponse($row, 'Data store successfully', 'plain');
    }

    public function index(Request $request)
    {
        //check register
        if (!isset($request->register) && !isset($request->register_start)) {
            $request->request->add(['register' => date('Y-m-d')]);
        }
        if (isset($request->page)) {
            $rows = Model::selectRaw('queues.*, counters.code as counter_code')
                ->join('services', 'services.id', '=', 'queues.service_id')
                ->join('counters', 'counters.id', '=', 'services.counter_id')
                ->FilterInput($request)->SetOrderBy($request)->with('service')
                ->paginate($request->per_page, ['*'], 'page', $request->page);
        } else {
            $rows = Model::selectRaw('queues.*, counters.code as counter_code')
                ->join('services', 'services.id', '=', 'queues.service_id')
                ->join('counters', 'counters.id', '=', 'services.counter_id')
                ->FilterInput($request)->SetOrderBy($request)->with('service')->get();
        }

        return sendResponse($rows, 'Data store successfully', 'plain');
    }

    public function call(Request $request)
    {
        $idSelected = $request->id;
        if ($idSelected <= 0) {
            return sendError('Data does not exist!', '', '404', 'plain');
        }

        //if data not exist
        if (!($row = Model::where('id', $idSelected)
            ->where(function ($qry) use ($request) {
                $qry->where('status', 'pending')
                ->orWhere('status', 'call');
            })
            ->exists())) {
            return sendError('Data does not exist!', '', '404', 'plain');
        }

        //get others with status call to pending
        $data = [
            'status' => 'pending',
        ];
        Model::join('services', 'services.id', '=', 'queues.service_id')->where('services.counter_id', $request->counterId)->where('queues.status', 'call')->where('queues.register', date('Y-m-d'))->update($data);

        //update selected status to call
        $data = [
            'status' => 'call',
        ];
        Model::where('id', $idSelected)->update($data);

        $request->request->add(['id' => $idSelected]);
        $row = Model::selectRaw('queues.*, counters.code as counter_code')
            ->join('services', 'services.id', '=', 'queues.service_id')
            ->join('counters', 'counters.id', '=', 'services.counter_id')
            ->FilterInput($request)->SetOrderBy($request)->with('service')->first();
        //return row
        if ($row) {
            //get active
            $rowActive = $this->redisplay($request);
            $key = $row->counter_code - 1;
            $this->restAPi('http://localhost:3001/send_nextQueue?key=' . $key . '&antrian=' . $row->number . '&passKey=P1VN3oi4t9j2zKp&loket=' . $row->counter_code . '&message=haloooo' . '&queueList=' . json_encode($rowActive->data) . '&queueCode=' . $row->code);
        } else {
            return sendError('Data fetch error: ', '', '404', 'plain');
        }
        return sendResponse($idSelected, 'Data store successfully', 'plain');
    }

    public function done(Request $request)
    {
        //if data not exist
        if (!($row = Model::where('id', $request->id)->exists())) {
            return sendError('Data does not exist!', '', '404', 'plain');
        }
        //update status
        $data = [
            'status' => 'close',
            'end_at' => date("Y-m-d H:i:s"),
        ];
        Model::where('id', $request->id)->update($data);
        $row = Model::selectRaw('queues.*, counters.id as counter_id')
            ->join('services', 'services.id', '=', 'queues.service_id')
            ->join('counters', 'counters.id', '=', 'services.counter_id')
            ->FilterInput($request)->first();
        //$row = Model::find($request->id);
        //get next
        $request->request->add(['counter_id' => $row->counter_id]);
        $request->request->add(['number_selected' => $row->number]);
        $request->request->add(['register' => $row->register]);
        $request->request->remove('id');
        $row = $this->next($request);
        if (!$row->success) {
            return sendError($row->message, '', '404', 'plain');
        }

        return sendResponse($row->data, 'Data store successfully', 'plain');
    }

    public function process(Request $request)
    {
        //if data not exist
        if (!($row = Model::where('id', $request->id)
            ->where(function ($qry) use ($request) {
                $qry->where('status', 'pending')
                ->orWhere('status', 'call');
            })
            ->exists())) {
            return sendError('Data does not exist!', '', '404', 'plain');
        }
        //update status
        $data = [
            'status' => 'active',
            'start_at' => date("Y-m-d H:i:s"),
            'end_at' => date("Y-m-d H:i:s"),
        ];
        Model::where('id', $request->id)->update($data);

        return sendResponse($request->id, 'Data store successfully', 'plain');
    }

    public function store(Request $request)
    {
        //check register
        if (!isset($request->register)) {
            $request->request->add(['register' => date('Y-m-d')]);
        }
        //get last queue
        $serviceRow = Service::find($request->service_id);
        $request->request->add(['order_by' => 'number']);
        $request->request->add(['order_by_dir' => 'DESC']);
        $request->request->add(['counter_id' => $serviceRow->counter_id]);
        $row = Model::selectRaw('queues.*')
            ->join('services', 'services.id', '=', 'queues.service_id')
            ->join('counters', 'counters.id', '=', 'services.counter_id')
            ->FilterInput($request)->SetOrderBy($request)->with('service')->first();
        //if code is empty
        if (!isset($request->code)) {
            if (!$row) {
                $prefix = $serviceRow->counter_id;
            } else {
                $prefix = $row->service->counter->id;
            }
            if ($prefix == 1) {
                $newPrefix = "A";
            } else if ($prefix == 2) {
                $newPrefix = "B";
            } else {
                $newPrefix = "C";
            }
            $code = $this->codeGenerate($newPrefix . "-");
            $request->request->add(['code' => $code]);
        }
        $qr_code = Hash::make($code . date('Y-m-d'));

        //validate input
        $data = array_merge($request->all());
        $validator = Validator::make($data, $this->createRequest->rules(), $this->createRequest->messages());
        if ($validator->fails()) {
            return sendError('Data store error: ' . $validator->errors()->all()[0], '', '404', 'plain');
        } else {
            $data = [
                'code' => $code,
                'register' => date('Y-m-d'),
                'number' => $row ? $row->number + 1 : 1,
                'service_id' => $request->service_id,
                'status' => 'pending',
                'qr_code' => $qr_code,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
            ];
            $store = Model::create($data);
            $result = Model::selectRaw('queues.*')->where('id', $store->id)->with('service')->first();

            $this->restAPi('http://localhost:3001/send_store?passKey=P1VN3oi4t9j2zKp&loket=' . $prefix);

            return sendResponse($result, 'Data store successfully', 'plain');
        }
    }

    public function restart(Request $request)
    {
        //check register
        if (!isset($request->register)) {
            $request->request->add(['register' => date('Y-m-d')]);
        }
        $request->request->add(['status' => 'active']);
        $row = Model::selectRaw('queues.*, counters.code as counter_code')
            ->join('services', 'services.id', '=', 'queues.service_id')
            ->join('counters', 'counters.id', '=', 'services.counter_id')
            ->FilterInput($request)->SetOrderBy($request)->with('service')->first();
        //if active is empty
        if (!$row) {
            //get min number and set as selected
            //$request->request->remove('status');
            $request->request->add(['status' => 'pending']);
            $request->request->add(['restart' => '1']);
            $row = Model::selectRaw('queues.*, counters.code as counter_code')
                ->join('services', 'services.id', '=', 'queues.service_id')
                ->join('counters', 'counters.id', '=', 'services.counter_id')
                ->FilterInput($request)->SetOrderBy($request)->with('service')->first(); //first() toSql()
        }
        //return row selected
        return sendResponse($row, 'Data store successfully', 'plain');
    }

    public function selected(Request $request)
    {
        $row = Model::selectRaw('queues.*, counters.code as counter_code')
            ->join('services', 'services.id', '=', 'queues.service_id')
            ->join('counters', 'counters.id', '=', 'services.counter_id')
            ->FilterInput($request)->SetOrderBy($request)->with('service')->first();
        //return row

        return sendResponse($row, 'Data store successfully', 'plain');
    }

    public function next(Request $request)
    {
        //check register
        if (!isset($request->register)) {
            $request->request->add(['register' => date('Y-m-d')]);
        }
        $request->request->add(['order_by' => 'number']);
        $request->request->add(['order_by_dir' => 'ASC']);
        $request->request->add(['status' => 'pending']);
        $request->request->add(['next' => $request->number_selected]);
        $row = Model::selectRaw('queues.*, counters.code as counter_code')
            ->join('services', 'services.id', '=', 'queues.service_id')
            ->join('counters', 'counters.id', '=', 'services.counter_id')
            ->FilterInput($request)->SetOrderBy($request)->with('service')->first();
        //return row

        return sendResponse($row, 'Data store successfully', 'plain');
    }

    public function back(Request $request)
    {
        //check register
        if (!isset($request->register)) {
            $request->request->add(['register' => date('Y-m-d')]);
        }
        $request->request->add(['order_by' => 'number']);
        $request->request->add(['order_by_dir' => 'DESC']);
        $request->request->add(['status' => 'pending']);
        $request->request->add(['back' => $request->number_selected]);
        $row = Model::selectRaw('queues.*, counters.code as counter_code')
            ->join('services', 'services.id', '=', 'queues.service_id')
            ->join('counters', 'counters.id', '=', 'services.counter_id')
            ->FilterInput($request)->SetOrderBy($request)->with('service')->first();
        //return row
        return sendResponse($row, 'Data store successfully', 'plain');
    }

    public function restAPi($url)
    {
        // pemanggilan API start
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array('unit' => 'pcs'),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ',
                'Accept: application/json',
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        // end
    }

    public function codeGenerate($prefix)
    {
        $row = Model::whereDate('register', '=', date('Y-m-d'))->where('code', 'like', $prefix . '%')->orderBy('code', 'desc')->first();
        if ($row && (strlen($row->code) == 5)) {
            $last_code = $row->code;
        } else {
            $prefix = $prefix;
            $last_code = acc_codedef_generate($prefix, 5);
        }
        $code = acc_code_generate($last_code, 5, 2);
        return $code;
        // return response()->json($code);
    }
}
