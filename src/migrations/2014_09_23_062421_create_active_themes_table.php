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
			$table->increments('active_theme_id');
			$table->string('active_theme_name', 150);
			$table->integer('active_theme_order')->default(0);
			$table->boolean('active_theme_is_enabled')->default(0);;
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
