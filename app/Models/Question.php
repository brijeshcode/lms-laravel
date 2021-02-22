<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Quiz;


class Question extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function quizs()
    {
        return $this->belongsToMany(Quiz::class, 'quiz_items', 'quiz_id', 'question_id')->orderBy('display_order', 'asc');
    }
}
