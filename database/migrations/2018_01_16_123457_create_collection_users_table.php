<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCollectionUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('collection_users');
    }
}
