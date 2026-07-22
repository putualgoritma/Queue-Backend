<?php

namespace Modules\Queue\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuestbookUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // 'register' => 'required',
            // 'number' => 'required',
            // 'service_id' => 'required',
            // 'status' => 'required'
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
}
