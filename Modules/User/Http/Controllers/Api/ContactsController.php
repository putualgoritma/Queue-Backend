<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as Controller;
use Modules\User\Services\ContactService;
use Modules\User\Transformers\ContactResource;
use Validator;

class ContactsController extends Controller
{

    public function index(Request $request, ContactService $contactService)
    {
        //set contact
        $contacts = $contactService->index($request);
        if (!$contacts->success) {
            return sendError($contacts->message, '', '404');
        }

        return sendResponse($contacts->data, 'Contact retrieved successfully.');
    }

    public function store(Request $request, ContactService $contactService)
    {
        //set contact
        $contact = $contactService->store($request);
        if (!$contact->success) {
            return sendError($contact->message, '', '404');
        }

        return sendResponse(new ContactResource($contact->data), 'Contact register successfully.');
    }

    public function show($id)
    {
        $contact = Contact::find($id);

        if (is_null($contact)) {
            return sendError('User not found.');
        }

        return sendResponse(new ContactResource($contact), 'User retrieved successfully.');
    }

    public function update(Request $request, ContactService $contactService)
    {
        //set contact
        $contact = $contactService->update($request);
        if (!$contact->success) {
            return sendError($contact->message, '', '404');
        }

        return sendResponse(new ContactResource($contact->data), 'Contact update successfully.');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return sendResponse([], 'User deleted successfully.');
    }
}
