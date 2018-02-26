<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPostsForCms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cms_posts', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->text('post_title')->nullable();
            $table->bigInteger('post_author')->nullable();
            $table->longText('post_content')->nullable();
            $table->longText('custom')->nullable();
            $table->text('post_excerpt')->nullable();
            $table->string('post_password')->nullable();
            $table->string('post_name')->nullable();
            $table->bigInteger('post_parent')->nullable();
            $table->string('post_type')->nullable();
            $table->integer('menu_order')->nullable();
            $table->string('post_mime_type')->nullable();
            $table->string('status')->nullable();
            $table->string('taxonomy')->nullable()->default('post');
            $table->string('template')->nullable();
            $table->index(['post_name', 'id']);

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
        Schema::drop('cms_posts');
    }
}
