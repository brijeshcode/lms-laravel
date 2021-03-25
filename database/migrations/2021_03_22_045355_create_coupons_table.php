<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('coupon_code');
            $table->text('description')->nullable();
            $table->string('discount_type')->default('percent')->comment('discount_type: percent|fix');
            $table->float('coupon_amount', 8,2)->default('0');
            $table->date('coupon_expiry_date')->nullable()->comment('Coupon will expire on this date.');
            $table->bigInteger('usage_count')->default('0')->comment('Counts how many times it is been used.');

            $table->double('minimum_amount', 8,2)->nullable()->comment('minimum amount to avail the coupon');
            $table->double('maximum_amount', 8,2)->nullable()->comment('This field allows you to set the maximum spend (subtotal) allowed when using the coupon.');

            $table->text('customer_email', 8,2)->nullable()->comment('applicable on to the uses whose email is set here, add emails in comma seprated.');


            $table->bigInteger('usage_limit_per_user')->nullable()->comment('How many times this coupon can be used by an individual user. Uses billing email for guests, and user ID for logged in users.');

            $table->bigInteger('limit_usage_to_x_items')->nullable()->comment('The maximum number of individual items this coupon can apply to when using product discounts. Leave blank to apply to all qualifying items in cart');

            $table->bigInteger('usage_limit')->nullable()->comment('How many times this coupon can be used before it is void.');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupons');
    }
}
