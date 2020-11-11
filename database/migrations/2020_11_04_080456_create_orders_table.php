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
            $table->tinyInteger('status')->nullable()->comment('0:Open, 1:Saved, 2:Closed')->default(0);
            $table->decimal('price_total', 20, 4)->nullable()->default(0);
            $table->decimal('discount', 20, 4)->nullable()->default(0);
            $table->bigInteger('promotion_id')->nullable();
            $table->tinyInteger('discount_type')->nullable()->comment('1:Percent, 2:Fixed')->default(1);
            $table->decimal('final_price', 20, 4)->nullable()->default(0);
            $table->tinyInteger('payment_type')->nullable()->comment('0:Cash, 1:QRIS, 2:Debt')->default(0);
            $table->decimal('cogs', 20, 4)->nullable()->default(0);
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
