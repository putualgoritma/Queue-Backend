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

    public function index(Request $request)
    {
        //check register
        if (!isset($request->register)) {
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

    public function store(Request $request)
    {
        //get last queue
        $request->request->add(['order_by' => 'number']);
        $request->request->add(['order_by_dir' => 'DESC']);
        $row = Model::selectRaw('queues.*')
            ->join('services', 'services.id', '=', 'queues.service_id')
            ->join('counters', 'counters.id', '=', 'services.counter_id')
            ->FilterInput($request)->SetOrderBy($request)->with('service')->first();
        //if code is empty
        if (!isset($request->code)) {
            // $period = substr($request->register, 0, 4);
            $prefix = $row->service->counter->id;
            $code = $this->codeGenerate($prefix);
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
            ];
            $store = Model::create($data);
            $result = Model::selectRaw('queues.*')->where('id', $store->id)->with('service')->first();

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
                ->FilterInput($request)->SetOrderBy($request)->with('service')->first();
        }
        //return row selected
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
        $row = Model::selectRaw('queues.*, counters.code as counter_code')
            ->join('services', 'services.id', '=', 'queues.service_id')
            ->join('counters', 'counters.id', '=', 'services.counter_id')
            ->FilterInput($request)->SetOrderBy($request)->with('service')->first();
        //return row
        return sendResponse($row, 'Data store successfully', 'plain');
    }

    public function nextBAK(Request $request)
    {

        $queueCurrent = Model::selectRaw('queues.*, counters.code as counter_code')
            ->join('services', 'services.id', '=', 'queues.service_id')
            ->join('counters', 'counters.id', '=', 'services.counter_id')
            ->whereDate('register', '=', date('Y-m-d'))
            ->where('counter_id', $request->counter_id)
            ->where('status', 'call')
            ->orderBy('number', 'ASC')
            ->first();

        $data = [
            'status' => $request->status,
        ];

        if ($queueCurrent) {
            Model::where('id', $queueCurrent->id)->update($data);
        }

        $queue = Model::selectRaw('queues.*, counters.code as counter_code')
            ->join('services', 'services.id', '=', 'queues.service_id')
            ->join('counters', 'counters.id', '=', 'services.counter_id')
            ->whereDate('register', '=', date('Y-m-d'))
            ->where('counter_id', $request->counter_id)
            ->where('status', 'wait')
            ->orderBy('number', 'ASC')
            ->first();

        $data = [
            'status' => 'call',
        ];
        if ($queue) {
            $this->restAPi('http://localhost:3001/send_nextQueue?key=0&antrian=' . $queue->number . '&passKey=P1VN3oi4t9j2zKp&loket=' . 1 . '&message=haloooo');

            $result = Model::where('id', $queue->id)->update($data);
        } else {
            $queue = $queueCurrent;
        }

        return sendResponse($queue, 'Data store successfully', 'plain');
    }

    public function pass(Request $request)
    {
        $data = [
            'status' => 'done',
        ];
        $queue = Model::selectRaw('queues.*, counters.name as counter_name')
            ->join('services', 'services.id', '=', 'queues.service_id')
            ->join('counters', 'counters.id', '=', 'services.counter_id')
            ->where('id', $request->id)
            ->first();
        $result = Model::where('id', $request->id)->update($data);
        $this->restAPi('http://localhost:3001/send_nextQueue?antrian=' . $queue->number . '&passKey=P1VN3oi4t9j2zKp&loket=' . $queue->counter_name . '&message=haloooo
        ');
        return sendResponse($queue, 'Data store successfully', 'plain');
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
        if ($row && (strlen($row->code) == 4)) {
            $last_code = $row->code;
        } else {
            $prefix = $prefix;
            $last_code = acc_codedef_generate($prefix, 4);
        }
        $code = acc_code_generate($last_code, 4, 1);
        return $code;
        // return response()->json($code);
    }
}
