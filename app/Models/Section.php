<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\SectionItems;

class Section extends Model
{
    use SoftDeletes;
    use HasFactory;

     protected $fillable = [
        'title', 'description', 'course_id' ,'section_order'
    ];

    public static function boot() {
        parent::boot();
        static::deleting(function($section) {
             $section->items()->delete();
        });
    }

    public function items()
    {
        return $this->hasMany(SectionItems::class, 'section_id')->orderBy('display_order');
    }

    /*public function lessons()
    {
        // return $this->belongsToMany(Lesson::class, 'section_items', 'section_id', 'item_id')->where('item_type', '=', 'lesson')->as('couse_lessons')->withPivot('display_order')->orderBy('display_order', 'asc');


        return $this->hasMany(SectionItems::class, 'section_id')->where('item_type', '=', 'lesson')->orderBy('display_order');
    }*/

    public function lessons()
    {
        return $this->hasMany(SectionItems::class, 'section_id')->where('item_type', '=', 'lesson')->orderBy('display_order');
    }

    public function quizes()
    {
        return $this->hasMany(SectionItems::class, 'section_id')->where('item_type', '=', 'quiz')->orderBy('display_order');
    }
}
