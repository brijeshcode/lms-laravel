<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->bigInteger('class_type_id')->unsigned()->index();
            $table->bigInteger('service_id')->unsigned()->index();
            $table->bigInteger('service_type_id')->unsigned()->index()->nullable();
            $table->integer('starts')->default(0);
            $table->boolean('is_taxable')->defalut(0);
            $table->integer('tax_class_id')->nullable();
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
        Schema::dropIfExists('packages');
    }
}
