<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->unsigned()->nullable();
            $table->foreign('parent_id')->references('id')->on('media')->onDelete('cascade')->onUpdate('cascade');
            $table->boolean('ready')->default(false);
            $table->string('disk');
            $table->string('context')->nullable();
            $table->string('title')->nullable();
            $table->string('caption')->nullable();
            $table->string('filename');
            $table->string('orig_filename')->nullable();
            $table->string('directory')->nullable();
            $table->string('mime')->nullable();
            $table->string('extension')->nullable();
            $table->unsignedInteger('size')->default(0);
            $table->json('filters')->nullable();
            $table->json('conversions')->nullable();
            $table->json('meta')->nullable();
            $table->json('tags')->nullable();
            $table->unsignedInteger('order')->nullable();
            $table->timestamps();
        });

        Schema::create('mediables', function($table) {
			$table->increments('id');
            $table->integer('model_id')->unsigned();
            $table->foreign('model_id')->references('id')->on('media')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('mediable_id')->unsigned();
            $table->string('mediable_type');
		});
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('mediables');
        Schema::dropIfExists('media');
    }
}
