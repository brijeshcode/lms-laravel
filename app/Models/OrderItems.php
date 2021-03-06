<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItems extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'order_id', 'course_id', 'course_title', 'price', 'sell_price', 'user_id'
    ];
}
