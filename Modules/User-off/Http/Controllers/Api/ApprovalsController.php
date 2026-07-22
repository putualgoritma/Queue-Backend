<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as Controller;
use Modules\User\Entities\Approvals;
use Modules\User\Services\ApprovalService;

class ApprovalsController extends Controller
{

    public function index(Request $request, ApprovalService $approvalService)
    {
        //set approval
        $approval = $approvalService->index($request);
        if (!$approval->success) {
            return sendError($approval->message, '', '404');
        }

        return sendResponse($approval->data, 'Request register successfully.');
    }

    public function store(Request $request, ApprovalService $approvalService)
    {
        //set approval
        $approval = $approvalService->store($request);
        if (!$approval->success) {
            return sendError($approval->message, '', '404');
        }

        return sendResponse($approval->data, 'Approval register successfully.');
    }

    public function patch(Request $request, ApprovalService $approvalService)
    {
        //set approval
        $approval = $approvalService->patch($request);
        if (!$approval->success) {
            return sendError($approval->message, '', '404');
        }
        //event(new UserEvent($approval->data));

        return sendResponse($approval->data, 'Status approved successfully.');
    }
}
