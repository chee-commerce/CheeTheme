<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActiveThemesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('active_themes', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name', 150);
			$table->integer('theme_order')->default(0);
			$table->boolean('is_enabled', 0);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('active_themes');
	}

}
