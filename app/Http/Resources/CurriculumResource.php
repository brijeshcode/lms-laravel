<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CurriculumResource extends JsonResource
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
            'name' => $this->title,
            'description' => $this->description,
            // 'itemss' => $this->items,
            'display_order' => $this->section_order,
            'items' => CurriculumItemsResource::collection($this->whenLoaded('items')),
        ];
    }
}
