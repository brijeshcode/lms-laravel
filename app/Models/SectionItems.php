<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Section;
use App\Models\Lesson;
use App\Models\Quiz;

class SectionItems extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'item_id', 'item_type', 'section_id', 'course_id' ,'display_order'
    ];



    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function getItemTypeAttribute($value)
    {
        return $value;
    }

    public function item()
    {
    	if (1) {
    		return $this->lessons();
    	}else{
        	return $this->quizes() ;
    	}
    }

    public function lessons()
    {
        return $this->hasOne(Lesson::class, 'id', 'item_id');
        // ->where($this->item_type, '=', 'lesson') ;
    }

    public function activeLesson()
    {
        return $this->belongsTo(Lesson::class,'item_id', 'id')->where('status', 'active');
    }

    public function quizes()
    {
        return $this->hasOne(Quiz::class, 'id', 'item_id') ;
    }

    public function activeQuiz()
    {
        return $this->belongsTo(Quiz::class,'item_id', 'id')->where('status', 'active');
    }
}
