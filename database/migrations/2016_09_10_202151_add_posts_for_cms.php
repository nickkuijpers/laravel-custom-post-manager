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
            $table->text('post_title');
            $table->bigInteger('post_author');
            $table->longText('post_content');
            $table->text('post_excerpt');
            $table->string('post_password');
            $table->string('post_name');
            $table->bigInteger('post_parent');
            $table->string('post_type');
            $table->integer('menu_order');
            $table->string('status');
            $table->string('template');
            $table->timestamps();

            $table->index(['post_name', 'id']);
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
