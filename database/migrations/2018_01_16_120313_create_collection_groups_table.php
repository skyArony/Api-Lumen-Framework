<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCollectionGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('collection_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('collection_key', 30)->comment('collection唯一性key');
            $table->string('group_key', 30)->comment('group唯一性key');
            $table->foreign('collection_key')->references('collection_key')->on('api_collections')->onDelete('cascade');
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
        Schema::dropIfExists('collection_groups');
    }
}
