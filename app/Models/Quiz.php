<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\QuizItem;
use App\Models\Question;

class Quiz extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    /*public function questions()
    {
        return $this->hasMany(QuizItem::class, 'quiz_id', 'id')->orderBy('display_order', 'asc');
    }*/

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'quiz_items', 'quiz_id', 'question_id')->as('quiz_question')->withPivot('display_order')->orderBy('display_order', 'asc');
    }
}
