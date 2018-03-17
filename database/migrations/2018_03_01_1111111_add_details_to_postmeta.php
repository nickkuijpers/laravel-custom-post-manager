<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDetailsToPostmeta extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('cms_postmeta', function (Blueprint $table) {
		    $table->bigInteger('menu_order')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('cms_postmeta', function (Blueprint $table) {
		    $table->dropColumn('menu_order');
		});
	}
}
