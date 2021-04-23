<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackageItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('package_id')->unsigned()->index();
            $table->bigInteger('batch_id')->unsigned()->index();
            $table->integer('week_days')->default(0);
            $table->integer('duration_month')->default(0);
            $table->integer('total_classes')->default(0);
            $table->integer('cost_per_class')->default(0);
            $table->integer('total_cost')->default(0);
            $table->integer('total_hours')->default(0);
            $table->string('class_duration_note')->nullable();
            $table->string('additional_note')->nullable();
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
        Schema::dropIfExists('package_items');
    }
}
