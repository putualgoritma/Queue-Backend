<?php

namespace Modules\User\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class RoleUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */

    public function rules($id = null)
    {
        return [
            'title' => ['unique:roles,title,' . $id],
            'permissions' => ['required', 'array'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response([
            'success' => false, 'message' => 'Role Registration error.', "data" => $validator->getMessageBag(),
        ], 400));
    }

    public function messages()
    {
        return [
            
        ];
    }
}
