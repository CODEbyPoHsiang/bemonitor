<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramSend extends Model
{
    protected $table = 'l_event';
    	
		// 要用IP變更狀態 所以要設成主鍵
    protected $primaryKey = "ip";
  
    public $incrementing = false;
  
    protected $keyType = 'string';
		//因為l_event表沒有時間戳記  所以停用
    public $timestamps = false; // 停用

}