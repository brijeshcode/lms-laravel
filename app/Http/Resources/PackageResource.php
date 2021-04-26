<?php

namespace App\Http\Resources;

use App\Http\Resources\PackagePricingResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'class' => $this->class->name,
            'service' => $this->service->name,
            'type' => $this->type->name,
            'taxable' => $this->is_taxable ? true : false,
            'prices' => PackagePricingResource::collection( $this->items)
        ];
    }
}
