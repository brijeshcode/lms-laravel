<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PackagePricingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return $item = [
            'id' => $this->id,
            'batch' => $this->batch->name,
            'batch_id' => $this->batch->id,
            'week_days' => $this->week_days,
            'duration_month' => $this->duration_month,
            'total_classes' => $this->total_classes,
            'cost_per_class' => $this->cost_per_class,
            'total_hours' => $this->total_hours,
            'class_duration_note' => $this->class_duration_note,
            'additional_note' => $this->additional_note,
        ];
    }
}
