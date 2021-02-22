<?php

namespace App\Http\Controllers\Payments\Gateways;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\PaymentMethod;

use App\Models\Course;
use App\Models\User;



class StripeController extends Controller
{
    //
    public function test()
    {
    	$couser = Course::findorfail(2);
    	$sellPrice = $couser->price;
    	$user = User::findorFail(1);

    	// $protal = $this->billPortal();

	    /*$stripeCustomer = $this->createStripeCustomer();
    	dd($stripeCustomer);*/



        return view('payments.test-stripe');

    	if ($user->hasDefaultPaymentMethod()) {
		    //
		}else{
			dd('no payment method');
		}

    	$paymentMethods = $user->paymentMethods();
    	// $paymentMethods = $user->defaultPaymentMethod();
    	dd($paymentMethods);

    	// customerId : cus_IxJxKJC15J6TbC


    	/*$stripeCharge = $user->charge(
	        100, $request->paymentMethodId
	    );*/
    	// $user = Cashier::findBillable($stripeId);

    	// $stripeCustomer = $user->createAsStripeCustomer();
    }

    public function makeSinglePayment($paymentMethodId)
    {
    	$user = User::findorFail(1);
    	try {
		    $stripeCharge = $user->charge(100, $paymentMethodId);
		} catch (Exception $e) {
		    dd($e.message());
		}

    	dd($stripeCharge);

    }
    public function billPortal()
    {
    	$user = User::findorFail(1);
    	return $stripeCustomer = $user->redirectToBillingPortal(route('voyager.dashboard'));
    }
    public function createStripeCustomer(){

    	$user = User::findorFail(1);

    	// $stripeCustomer = $user->createAsStripeCustomer();
    	return $stripeCustomer = $user->createOrGetStripeCustomer();
    }
    public function checkout($value='')
    {
    	# code...
    }
}
