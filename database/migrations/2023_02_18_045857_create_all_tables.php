<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('firstName');
            $table->string('lastName');
            $table->string('email')->unique();
            $table->string('avatar')->default('default.png');
        });

        Schema::create('usersappointments', function (Blueprint $table) {
          $table->id();
          $table->integer('user_id');
          $table->integer('local_id');
          $table->dateTime('ap_datetime');
      });

        Schema::create('local', function (Blueprint $table) {
          $table->id();
          $table->string('name');
          $table->integer('maxquantity')->default(0);
          $table->string('latitude');
          $table->string('longitude');
      });

        Schema::create('localreviews', function (Blueprint $table) {
          $table->id();
          $table->integer('local_id');
          $table->float('rate')->default(0);
      });

        Schema::create('localquality', function (Blueprint $table) {
          $table->id();
          $table->integer('local_id');
          $table->string('description'); //what does this local offer
          $table->float('price');
      });

        Schema::create('localtestimonials', function (Blueprint $table) {
          $table->id();
          $table->integer('local_id');
          $table->string('username'); //who commented this | get from user id
          $table->float('rate');
          $table->string('body'); //what did the user think of this place
      });
      
        Schema::create('localavaiability', function (Blueprint $table) {
          $table->id();
          $table->integer('local_id');
          $table->integer('weekday');
          $table->text('hours');
          $table->time('start_time');
          $table->time('end_time');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('all_tables');
    }
};
