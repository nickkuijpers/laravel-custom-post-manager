<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTaxonomyPivotToCms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cms_taxonomy', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('post_id')->unsigned();
            $table->bigInteger('taxonomy_post_id')->unsigned();

            $table->string('taxonomy')->nullable();

            $table->bigInteger('post_parent')->nullable();

            $table->foreign('post_id')->references('id')->on('cms_posts')->onDelete('cascade');
            $table->foreign('taxonomy_post_id')->references('id')->on('cms_posts')->onDelete('cascade');

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
        Schema::drop('cms_taxonomy');
    }
}
