<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PackageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return removeNullish([
            'id' => $this->id,
            'package_id' => $this->package_id,
            'avatar' => $this->owner?->avatar ?? Config('package.default_avatar'),
            'is_default' => $this->is_default ?? false,
            'name' => $this->name,
            'allocation' => $this->funds ? new FundCollection($this->funds) : null,
            // 'investment_amount' => $this->owner?->investment_amount ?? "0.000",
        ]);
    }
}
