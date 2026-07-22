<?php

namespace Modules\Queue\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Queue\Services\QueueService;

class QueueController extends Controller
{
    public function restart(Request $request, QueueService $service)
    {
        $row = $service->restart($request);
        if (!$row->success) {
            return sendError($row->message, '', '404');
        }
        return sendResponse($row->data, 'Data fetch successfully.');
    }
    
    public function index(Request $request, QueueService $service)
    {
        $data = $service->index($request);
        if (!$data->success) {
            return sendError($data->message, '', '404');
        }
        return $data;
    }

    public function done(Request $request, QueueService $service)
    {
        $data = $service->done($request);
        if (!$data->success) {
            return sendError($data->message, '', '404');
        }
        return $data;
    }

    public function process(Request $request, QueueService $service)
    {
        $data = $service->process($request);
        if (!$data->success) {
            return sendError($data->message, '', '404');
        }
        return $data;
    }

    public function store(Request $request, QueueService $service)
    {
        $data = $service->store($request);
        if (!$data->success) {
            return sendError($data->message, '', '404');
        }
        return $data;
    }

    public function back(Request $request, QueueService $service)
    {
        $data = $service->back($request);
        if (!$data->success) {
            return sendError($data->message, '', '404');
        }
        return $data;
    }

    public function call(Request $request, QueueService $service)
    {
        $row = $service->call($request);
        if (!$row->success) {
            return sendError($row->message, '', '404');
        }
        return sendResponse($row->data, 'Data fetch successfully.');
    }

    public function next(Request $request, QueueService $service)
    {
        $data = $service->next($request);
        if (!$data->success) {
            return sendError($data->message, '', '404');
        }
        return $data;

        // return response()->json(
        //     [
        //         'message' => 'k',
        //         'data' => $response
        //     ]
        // );
    }
}
