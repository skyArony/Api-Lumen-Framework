<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApiSys extends Migration
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

        Schema::create('api_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('group_key', 30)->unique()->comment('group唯一性key');
            $table->string('intro', 500)->comment('Group介绍');
            $table->timestamps();
        });

        Schema::create('api_collections', function (Blueprint $table) {
            $table->increments('id');
            $table->string('collection_key', 30)->unique()->comment('collection唯一性key');
            $table->boolean('istemp')->default(true)->comment('collection是否是临时，永久：false，临时：true，设置这属性是为了便于清理临时权限');
            $table->string('intro', 500)->comment('Collection介绍');
            $table->timestamps();
        });

        Schema::create('group_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('item_key', 30)->comment('API唯一性key');
            $table->string('group_key', 30)->comment('group唯一性key');
            $table->foreign('item_key')->references('item_key')->on('api_items')->onDelete('cascade');
            $table->foreign('group_key')->references('group_key')->on('api_groups')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('collection_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('collection_key', 30)->comment('collection唯一性key');
            $table->string('group_key', 30)->comment('group唯一性key');
            $table->foreign('collection_key')->references('collection_key')->on('api_collections')->onDelete('cascade');
            $table->foreign('group_key')->references('group_key')->on('api_groups')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('collection_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('collection_key', 30)->comment('collection唯一性key');
            $table->string('item_key', 30)->comment('API唯一性key');
            $table->foreign('collection_key')->references('collection_key')->on('api_collections')->onDelete('cascade');
            $table->foreign('item_key')->references('item_key')->on('api_items')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('collection_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('collection_key', 30)->comment('collection唯一性key');
            $table->string('email', 30)->comment('用户唯一性凭证');
            $table->timestamp('start_at')->comment('授权开始时间');
            $table->timestamp('end_at')->comment('授权结束时间');
            $table->foreign('collection_key')->references('collection_key')->on('api_collections')->onDelete('cascade');
            $table->foreign('email')->references('email')->on('users')->onDelete('cascade');
            $table->timestamps();
        });

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
        //
    }
}
