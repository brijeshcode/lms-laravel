<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public static $wrap = 'course';
    public function toArray($request)
    {
        // return parent::toArray($request);

        return [
            'id' => $this->id,
            'name' => $this->title,
            'description' => $this->description,
            'duration' => $this->duration,
            'duration_unit' => $this->duration_unit,
            'pricing' => !is_null($this->pricing) ? json_decode($this->pricing) : $this->pricing,
            'thumbnail' => $this->featured_image,
            'curriculums' => CurriculumResource::collection($this->whenLoaded('curriculums')),

        ];
    }
}
