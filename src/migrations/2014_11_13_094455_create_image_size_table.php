<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImageSizeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('image_sizes', function(Blueprint $table)
		{
			$table->increments('image_size_id');
			$table->string('image_size_name', 32);
			$table->string('image_size_width', 5)->nullable()->default(null);
			$table->string('image_size_height', 5)->nullable()->default(null);
			$table->string('image_size_quality', 3)->nullable()->default(null);
			$table->string('image_size_usage', 200)->nullable()->default(null);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('image_sizes');
	}

}
