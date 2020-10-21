<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLMonitorTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $tableName = 'l_monitor';

    /**
     * Run the migrations.
     * @table l_monitor
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->increments('sn');
            $table->string('ip', 15)->nullable()->default('');
            $table->tinyInteger('u')->nullable()->default('0')->comment('登入者數目');
            $table->smallInteger('v')->nullable()->default('0')->comment('連線數(ESTABLISHED)');
            $table->smallInteger('w')->nullable()->default('0')->comment('連線數(TIME_WAIT)');
            $table->tinyInteger('x')->nullable()->default('0')->comment('Listen Port 數');
            $table->smallInteger('y')->nullable()->default('0')->comment('系統程序數');
            $table->dateTime('m_time')->nullable()->default(null);
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
