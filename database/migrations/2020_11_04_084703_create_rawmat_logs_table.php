<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRawmatLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rawmat_logs', function (Blueprint $table) {
            $table->id();
            $table->boolean('saved')->default(false);
            $table->tinyInteger('status')->nullable()->comment('1:Sell/Out, 2:Restock/In');
            $table->decimal('quantity', 20, 4)->nullable();
            $table->decimal('price_total', 20, 4)->nullable();
            $table->bigInteger('rawmat_id')->nullable();
            $table->bigInteger('order_id')->nullable();
            $table->bigInteger('order_log_id')->nullable();
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
        Schema::dropIfExists('rawmat_logs');
    }
}
