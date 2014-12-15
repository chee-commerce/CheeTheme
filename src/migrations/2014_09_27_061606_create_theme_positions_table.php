<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateThemePositionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('theme_positions', function(Blueprint $table)
		{
			$table->increments('theme_position_id');
			$table->integer('theme_id')->unsigned();
			$table->foreign('theme_id')->references('theme_id')->on('themes')->onDelete('cascade')->onUpdate('cascade');
			$table->string('theme_position_name', 150);
			$table->text('theme_position_description')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('theme_positions');
	}

}
