<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRawmatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rawmats', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->decimal('stock', 20, 4)->nullable();
            $table->decimal('limit', 20, 4)->nullable();
            $table->decimal('price', 20, 4)->nullable();
            $table->string('unit')->nullable();
            $table->string('img')->nullable();
            $table->tinyInteger('restock_notif')->nullable();
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
        Schema::dropIfExists('rawmats');
    }
}
