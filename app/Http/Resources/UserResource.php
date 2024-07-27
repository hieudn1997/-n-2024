<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'address' => $this->address,
            'identity_number' => $this->identity_number,
            'dob' => $this->dob,
            'gender' => $this->gender,
            'avatar' => $this->avatar,
            'is_verify' => (bool) $this->is_verify,
            'is_activated' => (bool) $this->email_verified_at,
            'identity_image_front' => $this->identity_image_front,
            'identity_image_back' => $this->identity_image_back,
            'issue_place' => $this->issue_place,
            'issue_date' => $this->issue_date,
            'valid_date' => $this->valid_date,
            'role' => $this->role,
            'created_at' => $this->created_at
        ];
    }
}
