<?php

namespace App\Models;

use App\Models\Section;
use App\Models\TaxClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory;
    use SoftDeletes;
    // protected $with = ['curriculums'];

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

    public function taxes()
    {
        return $this->belongsTo(TaxClass::class, 'tax_classes_id');
    }
}
