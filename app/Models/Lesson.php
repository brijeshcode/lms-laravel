<?php

namespace App\Models;

use App\Models\Comment;
use App\Models\SectionItems;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lesson extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function sectionItems()
    {
        return $this->belongsTo(SectionItems::class, 'item_id')->withTrashed();
    }


    public function comments()
    {
        return $this->hasMany(Comment::class, 'parent_id')->where('comment_for', 'lesson')->where('is_approved', true);
    }



}
