<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bmonitor extends Model 
{
    
  protected $table = 'b_monitor';
  
  protected $fillable = 
  [
    'hostname',
    'ip',
    'mac',
    'os',
    'main_group',
    'sub_group',
    'community',
    'monitor',
    'u_monitor',
    'v_monitor',
    'w_monitor',
    'x_monitor',
    'y_monitor',
    'u_threshold',
    'v_threshold',
    'w_threshold',
    'x_threshold',
    'y_threshold',
    'alert',
    'u_alert',
    'v_alert',
    'w_alert',
    'x_alert',
    'y_alert',
    'u_notice',
    'v_notice',
    'w_notice',
    'x_notice',
    'y_notice',
    'u_value',
    'v_value',
    'w_value',
    'x_value',
    'y_value',
    'note',
    'create_uid',
    'create_time',
    'update_uid',
    'update_time',
];
  // 定義主key
  protected $primaryKey = "ip";
  // 主鍵是否遞增
  public $incrementing = false;
// 主鍵型別
  protected $keyType = 'string';
//重命名時間戳記
  const CREATED_AT = 'create_time';
  const UPDATED_AT = 'update_time'; 
// 修改時間戳記的value值格式
  protected $casts = [
    'create_time' => 'datetime:Y-m-d H:i:s',
    'update_time' => 'datetime:Y-m-d H:i:s',
];
 
  // public function toArray()
  //   {
  //     return[
  //       'os'=>(if($this->os),
  //       ];
  //   }


}