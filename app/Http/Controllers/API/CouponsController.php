<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponsController extends Controller
{
	protected $response = [
        'status' => 200,
        'success' => true,
        'message' => '',
        'data' => '',
    ];


    public function get()
    {
        $this->response['message'] = 'Coupons List.';
    	$coupons = Coupon::select('coupon_code','description', 'discount_type', 'coupon_amount','coupon_expiry_date','usage_limit')->get();
        $this->response['data'] = $coupons;

        return response()->json($this->response, $this->response['status']);
    }

    public function couponDetails($couponCode)
    {
    	$coupon = Coupon::select('coupon_code','description', 'discount_type', 'coupon_amount','coupon_expiry_date','usage_limit')
    	->where('coupon_code', $couponCode)
    	->first();

    	if ($coupon) {
	        $this->response['message'] = 'Coupon Detail.';
    		$this->response['data'] = $coupon;
    	}else{
    		$this->response['success'] = true;
    		$this->response['message'] = 'Invalid Coupons code.';
    		$this->response['status'] = 400;
    	}
        return response()->json($this->response, $this->response['status']);
    }
}
