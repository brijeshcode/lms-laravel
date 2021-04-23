<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    protected $cart = array();
    protected $response = [
        'status' => 200,
        'success' => true,
        'message' => '',
        'data' => '',
    ];

    public function setCart()
    {
        if (\Auth::guard('api')->check()) {
            $userID = auth('api')->user()->getKey();
        }

        $cart = Cart::create([
            'key' => md5(uniqid(rand(), true)),
            'user_id' => isset($userID) ? $userID : null
        ]);
        return  $cart;
    }

    public function add(Request $request)
    {
        // validation

        $validated = Validator::make($request->all(), [
            'product_id' => 'required',
            'product_type' => 'required',
            'quantity' => 'required',
            'price' => 'required',
            // 'cartKey' => 'required',
            'quantity' => 'required|integer|gt:0',
        ]);

        if ($validated->fails()) {
           return response()->json(['error' =>$validated->errors()], 422);
        }

        $cartKey = '';

        $cartKey = $request->cartKey;
        if (isset($request->cartKey)) {
            if (! $cart = $this->getCart($request->cartKey)) {
                $this->response['status'] = 400;
                $this->response['message'] = 'Error: Invalid cart details [cartKey].';
                $this->response['success'] = false;
                return response()->json($this->response, $this->response['status']);
            }
        }else{
            $cart  = $this->setCart();
        }

        $cartKey = $cart->key;

        $this->updateOrAddItem($cartKey, $request, $cart);

        return response()->json($this->response, $this->response['status']);
    }

    // return cart data
    public function get($key)
    {

        $cart = $this->getCartData($key);
        if (!$cart) {
            $this->response['status'] = 400;
            $this->response['message'] = 'Invalid Cart Key!';
            $this->response['success'] = true;
            return response()->json($this->response, $this->response['status']);
        }

        if ($cart->items()->exists()) {
            foreach ($cart->items as  $item) {
                $matched = $this->confirmPrice($item->product_id, $item->price, $item->product_type);
                if (!$matched[0]) {
                    $this->remove($key, $item->product_id);
                }
            }

            $this->response['status'] = 200;
            $this->response['message'] = 'Cart Data';
            $this->response['success'] = true;
            $this->response['data'] = $this->formatCartData($key);
            return response()->json($this->response, $this->response['status']);
        }

        $this->response['status'] = 200;
        $this->response['message'] = 'Cart Empty!';
        $this->response['success'] = true;
        return response()->json($this->response, $this->response['status']);
    }

    function getCart($cartKey){
        $cart = $this->getCartData($cartKey);
        if ($cart) {
            return $cart;
        }
        return false;
    }

    public function remove($cartKey, $productId)
    {
        $cart = $this->getCart($cartKey);
        if ($cart) {
            foreach ($cart->items as $key => $item) {
                if ($item->product_id == $productId) {
                    $tempTitle = $item->product->title;
                    CartItem::destroy($item->id);
                    $this->response['status'] = 200;
                    $this->response['message'] = 'Product: "'.$tempTitle.'" removed successfully.';
                    $this->response['success'] = true;
                    return response()->json($this->response, $this->response['status']);
                }
            }

            $this->response['status'] = 400;
            $this->response['message'] = 'Error: Invalid Product, not exist in cart.';
            $this->response['success'] = false;
            return response()->json($this->response, $this->response['status']);
        }

        $this->response['status'] = 400;
        $this->response['message'] = 'Error: Invalid cart details.';
        $this->response['success'] = false;
        return response()->json($this->response, $this->response['status']);
    }

    public function clearCart($cartKey)
    {
        $cart = Cart::where('key', $cartKey)->first();
        if ($cart) {
            $cart->delete();
        }

        if($cart ){
            $this->response['status'] = 200;
            $this->response['message'] = 'Cart Cleared.';
        }else{
            $this->response['status'] = 400;
            $this->response['message'] = 'Cart Already Cleared.';
        }
        $this->response['success'] = true;
        return response()->json($this->response, $this->response['status']);
    }

    public function updateOrAddItem($cartKey, $request, $cart = '')
    {
        $priceStatus = $this->confirmPrice($request->product_id, $request->price, $request->product_type);
        if (!$priceStatus[0]) {
            $this->response['success'] = $priceStatus[0];
            $this->response['message'] = $priceStatus[1];
            $this->response['status'] = $priceStatus[2];
            return $this->response;
        }

        if ($cart) {
            $cartId = $cart->id;
        }else{
            if ($cart = $this->getCart($cartKey)) {
                $cartId = $cart->id;
            }else{

                $this->response['status'] = 400;
                $this->response['message'] = 'Error: Invalid cart details.';
                $this->response['success'] = false;
                return $this->response;
            }
        }

        $match = [
            'cart_id' => $cartId,
            'product_id' => $request->product_id,
        ];

        $price = $request->price;

        $item = [
            'cart_id' => $cartId,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'price' => $request->price,
        ];

        if ($cart->items()->exists() && $cartItem = $cart->items()->where($match)->first()) {

            $cartItem->quantity = $cartItem->quantity + $request->quantity;
            $cartItem->price = $request->price;
            if (isset($request->product_description)) {
                $cartItem->product_description = $request->product_description;
            }
            $cartItem->save();
        }else{
            if (isset($request->product_description)) {
                $item['product_description'] = $request->product_description;
            }
            $cartItem = CartItem::create($item);
        }

        if($cartItem){
            $this->response['status'] = 201;
            $this->response['message'] = 'Product: '.$cartItem->product->title.' added to cart.';
            $this->response['success'] = true;
            return $this->response;
        }

        return $cartItem;
    }


    public function getCartItem($cartId)
    {
        return $items = CartItem::where('cart_id', $cartId)->get();
    }

    public function countPro($cartKey)
    {
        $cart = $this->getCartData($cartKey);
        return $cart->items->count();
    }

    public function countQty($cartKey){
        $count = 0;
        $cart = $this->getCartData($cartKey);
        if ($cart && $cart->items()->exists()) {
            foreach ($cart->items as $item) {
                $count += $item->quantity;
            }
        }
        return $count;
    }

    public function cartTotal($cartKey)
    {
        $total = 0;
        $cart = $this->getCartData($cartKey);
        if ($cart && $cart->items()->exists()) {
            foreach ($cart->items as $item) {
                $total += $item->price;
            }
        }
        return $total;
    }


    // return array of boolean and string
    // boolean          : will determine the check status
    // string           : give the message what actually heppend
    // number           : will return status of request
    public function confirmPrice($productId, $price, $type)
    {
        if ($type == 'course') {
            $product = Course::whereNotNull('pricing')->find($productId);
            if (!is_null($product)) {
            $pricing = json_decode($product->pricing, true);
            if (!is_array($pricing)) {
                return [false, 'Error: Pricing not set properly.',400];
            }

            foreach ($pricing as $key => $actual_price) {
                if ($actual_price['sell_price'] == $price) {
                    return [true, 'Success: Matched',200];
                }
            }
                return [false, 'Error: Invalid price',400];
            }else{
                return [false, 'Error: Invalid product',400];
            }
        }
    }

    public function getCartData($key){
        $cart = Cart::select('id', 'key', 'status', 'coupon_code', 'user_id')
        ->with([
            'items:id,cart_id,product_id,product_type,product_description,quantity,price',
            'items.product:id,title,pricing,tax_classes_id,tax_status',
            'items.product.taxes:id,name,tax_name,rate,status',
            'coupon',
        ])
        ->where('key', '=' ,$key)
        ->first();

        if ($cart && is_null($cart->user_id)) {
            if (\Auth::guard('api')->check()) {
                $userID = auth('api')->user()->getKey();
                $cart->user_id = $userID;
                $cart->save();
                $this->getCartData($cart->key);
            }
        }
        return $cart;
    }

    public function formatCartData($key)
    {
        $cart = $this->getCartData($key);

        $data = new Cart();
        $data->key = $cart->key;
        $data->cart_total_quantity = 0;
        $data->cart_total_products = 0;
        $data->cart_total_tax = 0;
        $data->cart_sub_total = 0;
        $data->cart_discount = 0;
        $data->cart_total = 0;

        $data->discount_coupon = $cart->coupon_code;
        // $data->cart_total = 0;
        if ($cart && $cart->items()->exists()) {

            $product = array();
            $index = 0;

            foreach ($cart->items as $value) {
                $product[$index]['name'] = $value->product->title;
                $product[$index]['type'] = $value->product_type;
                $product[$index]['quantity'] = $value->quantity;
                $product[$index]['sub_total'] = $value->price;
                $product[$index]['tax_price_inclusive'] = setting('site.tax_price_inclusive');
                $product[$index]['taxable'] = false;
                $product[$index]['sub_total'] = $value->price * $value->quantity;
                $product[$index]['total'] = $product[$index]['sub_total'];


                if ($value->product->tax_status == 'taxable' && !is_null($value->product->taxes)) {
                    $product[$index]['taxable'] = true;
                    $product[$index]['tax']['rate'] = $value->product->taxes->rate;
                    $product[$index]['tax']['name'] = $value->product->taxes->name;
                    $product[$index]['tax']['tax_name'] = $value->product->taxes->tax_name;
                    $product[$index]['tax']['amount'] = ($value->product->taxes->rate / 100) * $product[$index]['sub_total'];

                    if (!$product[$index]['tax_price_inclusive']) {
                        $product[$index]['total'] = $product[$index]['sub_total'] + $product[$index]['tax']['amount'];
                    }else{
                        $product[$index]['total'] = $product[$index]['sub_total'];
                    }

                    $data->cart_total_tax += $product[$index]['tax']['amount'];

                }


                $data->cart_total_quantity += $product[$index]['quantity'];
                $data->cart_sub_total += $product[$index]['total'];
                $index++;
            }
            $data->cart_total_products = $cart->items->count();
            $data->product = $product;
        }

        if (!is_null($cart->coupon_code)) {
            if ($cart->coupon->discount_type == 'percent') {
                $data->cart_discount = ($cart->coupon->coupon_amount /100 ) * $data->cart_sub_total;
            }else{
                $data->cart_discount = $cart->coupon->coupon_amount;
            }
        }

        $data->cart_total = $data->cart_sub_total - $data->cart_discount;

        return $data;
    }


    public function applyCoupon($cartKey, $couponCode)
    {
        $cart   = $this->getCartData($cartKey);

        if (is_null($cart)) {
            $this->response['success'] = false;
            $this->response['message'] = 'Invalid Cart key: cart not found on given key.';
            $this->response['status'] = 400;
            $this->response['data'] = ['cart_key' => $cartKey];
            return response()->json($this->response, $this->response['status']);
        }

        if (!is_null($cart->coupon_code) && $cart->coupon_code == $couponCode) {
            $this->response['success'] = false;
            $this->response['message'] = 'Coupon allready applied, cart data.';
            $this->response['status'] = 400;
            $this->response['data'] = $this->formatCartData($cartKey);
            return response()->json($this->response, $this->response['status']);
        }


        $coupon = Coupon::where('coupon_code', $couponCode)
                // ->where('coupon_expiry_date', '<', Carbon::today())
                ->first();


        if ($coupon) {

            if (!is_null($coupon->coupon_expiry_date) || $coupon->coupon_expiry_date > Carbon::today()) {
                $this->response['success'] = false;
                $this->response['message'] = 'Coupon expired.';
                $this->response['status'] = 400;
                return response()->json($this->response, $this->response['status']);
            }

            $cart->coupon_code = $couponCode;
            $cart->save();
            $this->response['message'] = 'Coupon Detail.';
            $this->response['data'] = $this->formatCartData($cartKey);

        }else{
            $this->response['success'] = false;
            $this->response['message'] = 'Invalid Coupon code.';
            $this->response['status'] = 400;
        }
        return response()->json($this->response, $this->response['status']);
    }


}