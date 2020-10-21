<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLEventTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $tableName = 'l_event';

    /**
     * Run the migrations.
     * @table l_event
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->increments('sn');
            $table->string('ip')->nullable()->default('');
            $table->char('monitor_id', 1)->nullable()->default('');
            $table->string('event')->nullable()->default('');
            $table->dateTime('e_time')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
     public function down()
     {
       Schema::dropIfExists($this->tableName);
     }
}
