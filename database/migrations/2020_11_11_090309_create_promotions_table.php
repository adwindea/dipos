<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->boolean('status')->default(true);
            $table->tinyInteger('discount_type')->nullable()->comment('1:Percent, 2:Fixed');
            $table->string('code')->nullable();
            $table->decimal('amount', 20, 4)->nullable();
            $table->decimal('quantity', 20, 4)->nullable();
            $table->decimal('min_buy', 20, 4)->nullable();
            $table->decimal('max_discount', 20, 4)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('note', 500)->nullable();
            $table->bigInteger('user_id')->nullable();
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
        Schema::dropIfExists('promotions');
    }
}
