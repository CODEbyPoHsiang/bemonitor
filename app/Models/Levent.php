<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Levent extends Model
{
    protected $table = 'l_event';
    
    public function toArray(){
        {
            return[
                "SN"=>$this->sn,
                "IP"=>$this->ip,
                "monitor_id"=>$this->monitor_id,
                "事件類型"=>$this->event_type,
                "訊息"=>$this->event,
                "記錄時間"=>$this->e_time,
            ];      
          }
    }
}
