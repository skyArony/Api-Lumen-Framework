<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApiRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_records', function (Blueprint $table) {
            $table->increments('id');
            $table->string('item_key', 30)->comment('API唯一性key');
            $table->timestamp('use_time')->comment("接口调用时间");
            $table->string('email')->comment("调用者凭据");
            $table->integer('status')->comment("调用结果status");
            $table->integer('errcode')->comment("调用结果errcode");
            $table->string('errmsg', 100)->comment("调用结果errmsg");
            $table->foreign('item_key')->references('item_key')->on('api_items')->onDelete('cascade');
            $table->foreign('email')->references('email')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('api_records');
    }
}
