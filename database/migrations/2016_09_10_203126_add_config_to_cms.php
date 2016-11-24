<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConfigToCms extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cms_config', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('option_name')->nullable();
			$table->longText('option_value')->nullable();
			$table->string('group')->nullable()->default('default');
			$table->timestamps();

			$table->index(['option_name', 'id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('cms_config');
	}
}
