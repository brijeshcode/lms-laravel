<?php

namespace App\Models;

use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    protected $fillable = ['parent_id', 'comment', 'comment_for', 'is_approved', 'user_id'];

    protected $casts = [
        'is_approved' => 'boolean',
    ];
    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'id');
    }
}
