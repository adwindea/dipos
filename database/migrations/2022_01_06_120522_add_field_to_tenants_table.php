<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldToTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('receipt_note', 500)->nullable()->after('logo');
            $table->string('currency', 5)->nullable()->after('logo');
            $table->string('number_format', 20)->nullable()->after('logo');
            $table->string('smtp_host', 100)->nullable()->after('receipt_note');
            $table->string('smtp_port', 100)->nullable()->after('smtp_host');
            $table->string('smtp_user', 100)->nullable()->after('smtp_port');
            $table->string('smtp_password', 100)->nullable()->after('smtp_user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tenants', function (Blueprint $table) {
            //
        });
    }
}
