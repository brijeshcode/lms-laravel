<?php

namespace App\Models;

use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cart extends Model
{
    use SoftDeletes;
    use HasFactory;

	protected $fillable = ['key','user_id', 'status'];


	public static function boot() {
        parent::boot();

        static::deleting(function($cart) {

        	$cart->items()->each(function($item){
             	$item->delete();
        	});
        });
    }


    public function items() {
        return $this->hasMany(CartItem::class, 'cart_id');
    }

    public function Coupon() {
        return $this->hasOne(Coupon::class, 'coupon_code', 'coupon_code');
    }

}
