<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'date', 'order_total', 'order_sub_total', 'order_status', 'user_id'
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function($model)
        {
            // $model->user_id = Auth()->user()->id;
            $model->created_by = Auth()->user()->id;
            $model->actor_ip = \Request::ip();
        });

        self::created(function($model){

        });

        static::updated(function($model)
        {

        });


        static::deleting(function($model)
        {

        });
    }

    public function items()
    {
        return $this->hasMany(OrderItems::class, 'order_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
