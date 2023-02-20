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
      //Alterar a tabela user adicionando o campo 'password'
      Schema::table('users', function(Blueprint $table) {
      $table->string('password')
        ->after('email');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      //Exclusão da coluna
      Schema::table('users', function(Blueprint $table) {
        $table->dropColumn('password');
      });
    }
};
