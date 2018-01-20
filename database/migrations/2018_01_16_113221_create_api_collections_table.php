<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApiCollectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_collections', function (Blueprint $table) {
            $table->increments('id');
            $table->string('collection_key', 30)->unique()->comment('collection唯一性key');
            $table->boolean('istemp')->default(true)->comment('collection是否是临时，永久：false，临时：true，设置这属性是为了便于清理临时权限');
            $table->string('intro', 500)->comment('Collection介绍');
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
        Schema::dropIfExists('api_collections');
    }
}
