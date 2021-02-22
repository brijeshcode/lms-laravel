<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Section;

class Course extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function curriculums()
    {
        return $this->hasMany(Section::class, 'course_id')->orderBy('section_order');
    }

    public static function boot() {
        parent::boot();

        static::deleting(function($course) {
        	$course->curriculums()->each(function($section){
             	// $section->items()->delete();
             	$section->delete();
        	});
        });
    }
}
