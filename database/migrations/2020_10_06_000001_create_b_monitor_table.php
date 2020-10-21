<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBMonitorTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $tableName = 'b_monitor';

    /**
     * Run the migrations.
     * @table b_monitor
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->string('hostname', 50)->comment('主機名稱');
            $table->increments('ip')->comment('IP');
            $table->string('mac', 17)->comment('MAC Address');
            $table->string('os', 1)->default('l')->comment('主機OS');
            $table->string('main_group', 20)->comment('主群組');
            $table->string('sub_group', 20)->comment('副群組');
            $table->string('community', 30)->default('be5310b363d96f80')->comment('SNMP Community');
            $table->tinyInteger('monitor')->default('0')->comment('是否監控(總開關)');
            $table->tinyInteger('u_monitor')->default('1')->comment('是否監控使用者登入數');
            $table->tinyInteger('v_monitor')->default('0')->comment('是否監控連線數(ESTABLISHED)');
            $table->tinyInteger('w_monitor')->default('0')->comment('是否監控連線數(TIME_WAIT)');
            $table->tinyInteger('x_monitor')->default('0')->comment('是否監控 Listen Port 數');
            $table->tinyInteger('y_monitor')->default('0')->comment('是否監控系統程序數');
            $table->tinyInteger('u_threshold')->default('0');
            $table->smallInteger('v_threshold')->default('20');
            $table->smallInteger('w_threshold')->default('100');
            $table->tinyInteger('x_threshold')->default('0');
            $table->unsignedTinyInteger('y_threshold')->default('0');
            $table->tinyInteger('alert')->default('0')->comment('異常');
            $table->tinyInteger('u_alert')->default('0');
            $table->tinyInteger('v_alert')->default('0');
            $table->tinyInteger('w_alert')->default('0');
            $table->tinyInteger('x_alert')->default('0');
            $table->tinyInteger('y_alert')->default('0');
            $table->tinyInteger('u_notice')->default('1');
            $table->tinyInteger('v_notice')->default('0');
            $table->tinyInteger('w_notice')->default('0');
            $table->tinyInteger('x_notice')->default('0');
            $table->tinyInteger('y_notice')->default('0');
            $table->tinyInteger('u_value');
            $table->smallInteger('v_value')->default('0');
            $table->smallInteger('w_value');
            $table->tinyInteger('x_value');
            $table->smallInteger('y_value')->default('0');
            $table->string('note')->comment('備註');
            $table->string('create_uid', 20)->comment('建立人員');
            $table->dateTime('create_time')->comment('建立時間');
            $table->string('update_uid', 20)->comment('更新人員');
            $table->dateTime('update_time')->comment('更新時間');
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
