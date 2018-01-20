<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('item_key', 30)->comment('API唯一性key');
            $table->string('group_key', 30)->comment('group唯一性key');
            $table->foreign('item_key')->references('item_key')->on('api_items')->onDelete('cascade');
            $table->foreign('group_key')->references('group_key')->on('api_groups')->onDelete('cascade');
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
        Schema::dropIfExists('group_items');
    }
}
