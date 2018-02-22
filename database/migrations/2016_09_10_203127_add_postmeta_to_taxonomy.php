<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPostmetaToTaxonomy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cms_taxonomymeta', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('taxonomy_id', false, true);
            $table->string('meta_key');
            $table->longText('meta_value')->nullable();
            $table->string('group')->nullable();
            $table->longText('custom')->nullable();
            $table->timestamps();

            $table->index(['taxonomy_id']);

            $table->foreign('taxonomy_id')->references('id')->on('cms_taxonomy')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('cms_taxonomymeta');
    }
}
