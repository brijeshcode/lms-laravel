<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('cart_id')->unsigned()->index();
            $table->Integer('product_id')->unsigned()->index();
            $table->string('product_type')->default('course'); //product_type : course or package
            $table->string('product_description')->nullable();
            $table->unsignedInteger('quantity');
            $table->double('price',10,2)->default(0);
            $table->foreign('cart_id')->references('id')->on('carts')->constrained()->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('courses')->onDelete('cascade');
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
        // Schema::dropForeign('cart_items_cart_id_foreign');
        Schema::dropIfExists('cart_items');
    }
}
