<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApiItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('item_key', 30)->unique()->comment('API唯一性key');
            $table->string('url', 150)->comment('API URL地址');
            $table->string('method', 10)->comment('API 调用方法');
            $table->string('intro', 500)->comment('API介绍');
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
        Schema::dropIfExists('api_items');
    }
}
