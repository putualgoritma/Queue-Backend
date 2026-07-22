<?php

namespace Modules\User\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'email' => $this->email,
            'status' => $this->status,
            'type' => $this->type,
            'register' => $this->register,
            'approved' => $this->approved,
            'activated' => $this->activated,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
