<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Cms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
		Schema::create('activities', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('action');
            $table->string('model');
             $table->integer('model_id')->unsigned();
            $table->text('attributes');
            $table->text('original');
        });
		
		  Schema::create('admin_shortcuts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('url');
            $table->string('title');
            $table->timestamps();
            $table->unique(['user_id', 'url']);
        });
		
		 Schema::table('activities', function (Blueprint $table) {

            $table->integer('user_id')->nullable()->references('id')->on('users');
            $table->boolean('published')->default(false);
        });
		
		Schema::create('templates', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('title');
            $table->string('filename');
            $table->string('types');
            $table->boolean('available');
        });
         Schema::create('pages', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('template_id')->unsigned();
            $table->integer('parent_id')->unsigned()->nullable();
            $table->integer('position');
            $table->boolean('online')->default(false);
            $table->string('title');
            $table->string('slug');
            $table->text('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            
            $table->unique('slug');

        });
        Schema::create('contents', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('page_id')->unsigned();
            $table->integer('position');
            $table->text('content');
            $table->index('page_id');
        });

        Schema::create('elements', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('page_id')->unsigned();
            $table->integer('position');
            $table->boolean('section');
            $table->date('date_published');
            $table->string('type');
            $table->string('subtype');
            $table->string('title');
            $table->string('subtitle');
            $table->string('slug');
            $table->text('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->text('summary');
            $table->text('content');
            $table->text('json');

            $table->index('page_id');
            $table->unique('slug');
        });

        Schema::table('pages', function (Blueprint $table) {
            //$table->foreign('parent_id')->references('id')->on('pages');
            $table->foreign('template_id')->references('id')->on('templates');
        });

        Schema::table('contents', function (Blueprint $table) {
            $table->foreign('page_id')->references('id')->on('pages')->onDelete('cascade');
        });

        Schema::table('elements', function (Blueprint $table) {
            $table->foreign('page_id')->references('id')->on('pages')->onDelete('cascade');
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
		Schema::dropIfExists('activities');
		
        Schema::dropIfExists('admin_shortcuts');
        Schema::dropIfExists('elements');
        Schema::dropIfExists('contents');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('templates');
    }
}
