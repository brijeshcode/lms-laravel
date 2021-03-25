<?php

namespace App\Models;

use App\Models\Cart;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CartItem extends Model
{
	protected $fillable = ['product_id', 'product_type', 'cart_id', 'quantity', 'price', 'product_description'];
    use SoftDeletes;
    use HasFactory;

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->hasOne(Course::class,'id', 'product_id');
    }
}
