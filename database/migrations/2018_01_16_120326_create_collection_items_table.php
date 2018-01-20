<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCollectionItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('collection_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('collection_key', 30)->comment('collection唯一性key');
            $table->string('item_key', 30)->comment('API唯一性key');
            $table->foreign('collection_key')->references('collection_key')->on('api_collections')->onDelete('cascade');
            $table->foreign('item_key')->references('item_key')->on('api_items')->onDelete('cascade');
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
        Schema::dropIfExists('collection_items');
    }
}