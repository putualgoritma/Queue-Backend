<?php

namespace Modules\User\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */

    public function rules($id = null)
    {
        return [
            'code' => ['max:12', 'unique:users,code,' . $id],
            'email' => ['email', 'unique:users,email,' . $id],
            'password' => [],
            'c_password' => ['same:password'],
            'register' => ['date'],
            'type' => ['in:admin,member,staff,supplier,public'],
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
            'success' => false, 'message' => 'User Registration error.', "data" => $validator->getMessageBag(),
        ], 400));
    }

    public function messages()
    {
        return [
            'code.required' => 'Kode tidak boleh kosong.',
            'code.max' => 'Kode maximum 10.',
            'code.unique' => 'Kode tidak boleh sama.',
        ];
    }
}
