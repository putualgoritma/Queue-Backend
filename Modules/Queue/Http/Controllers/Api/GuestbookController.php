<?php

namespace Modules\Queue\Http\Controllers\Api;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as Controller;
use Modules\Queue\Services\GuestbookService;
use Validator;

class GuestbookController extends Controller
{

    public function index(Request $request, GuestbookService $guestbookService)
    {
        //set guestbook
        $guestbooks = $guestbookService->index($request);
        if (!$guestbooks->success) {
            return sendError($guestbooks->message, '', '404');
        }

        return sendResponse($guestbooks->data, 'Guestbook retrieved successfully.');
    }

    public function store(Request $request, GuestbookService $guestbookService)
    {
        //set guestbook
        $guestbook = $guestbookService->store($request);
        if (!$guestbook->success) {
            return sendError($guestbook->message, '', '404');
        }

        return sendResponse($guestbook->data, 'Guestbook register successfully.');
    }

    public function show($id)
    {
        $guestbook = Guestbook::find($id);

        if (is_null($guestbook)) {
            return sendError('Queue not found.');
        }

        return sendResponse($guestbook, 'Queue retrieved successfully.');
    }

    public function update(Request $request, GuestbookService $guestbookService)
    {
        //set guestbook
        $guestbook = $guestbookService->update($request);
        if (!$guestbook->success) {
            return sendError($guestbook->message, '', '404');
        }

        return sendResponse($guestbook->data, 'Guestbook update successfully.');
    }

    public function destroy(Guestbook $guestbook)
    {
        $guestbook->delete();

        return sendResponse([], 'Queue deleted successfully.');
    }
}
