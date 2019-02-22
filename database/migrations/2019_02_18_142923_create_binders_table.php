<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Binders_interview', function (Blueprint $table) {
            $table->increments('idtblBinder');
            $table->unsignedInteger('Parent')
                ->nullable()
                ->default(null);
            $table->unsignedInteger('lft');
            $table->unsignedInteger('rgt');
            $table->unsignedInteger('idtblDatabaseIndexU');
            $table->boolean('Deleted')
                ->default(0);
            $table->string('BinderName', 190)->collation('utf8_bin');
            $table->index(['lft', 'idtblDatabaseIndexU'], 'unique_left');
            $table->index(['rgt', 'idtblDatabaseIndexU'], 'unique_right');
            $table->index(['Parent', 'idtblDatabaseIndexU', 'BinderName'], 'unique_name_per_parent');
            $table->index('idtblDatabaseIndexU', 'index_dbID');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Binders_interview');
    }
}
