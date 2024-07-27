<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PackageCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return $this->collection->map(function ($data) {
            return removeNullish([
                'avatar' => $data->avatar ?? Config('package.default_avatar'),
                'id' => $data->id,
                'name' => $data->name,
                // 'year_average' => +$data->year_average,
                // 'investment_amount' => $data->investment_amount
            ]);
        });
    }
}
