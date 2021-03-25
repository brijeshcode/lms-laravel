<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CurriculumItemsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $item = [
            'id' => $this->id,
            'type' => $this->item_type,
            'display_order' => $this->display_order,
        ];

        if ($this->item_type == 'lesson') {
            $lesson = $this->activeLesson;

            if (isset($lesson->title)) {
                $item['lesson_id'] = $lesson->id;
                $item['name'] = $lesson->title;
                $item['description'] = $lesson->description;

                $item['duration'] = $lesson->duration;
                $item['duration_unit'] = $lesson->duration_unit;
                $item['is_preview'] = $lesson->preview_lesson;
            }

        }else{

            $quiz = $this->activeQuiz;
            if (isset($quiz->title)) {
                $item['quiz_id'] = $quiz->id;
                $item['name'] = $quiz->title;
                $item['description'] = $quiz->description;
                $item['duration'] = $quiz->duration;
                $item['duration_unit'] = $quiz->duration_unit;
            }

        }

        return $item ;
    }
}
