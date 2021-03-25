<?php

namespace App\Models;

use App\Models\CouponMeta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use SoftDeletes;
    use HasFactory;

	protected $fillable = ['coupon_code','description', 'discount_type', 'coupon_amount','coupon_expiry_date','usage_count','minimum_amount','maximum_amount','customer_email','usage_limit_per_user','limit_usage_to_x_items','usage_limit'];

	public function meta()
    {
        return $this->hasMany(CouponMeta::class, 'coupon_id');
    }

}
