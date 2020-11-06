<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('order_number')->nullable();
            $table->tinyInteger('status')->nullable()->comment('0:Open, 1:Submit')->default(0);
            $table->decimal('price_total', 20, 4)->nullable()->default(0);
            $table->string('customer_name')->nullable();
            $table->string('customer_email', 500)->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->string('note', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->uuid('uuid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
