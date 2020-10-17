<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lmonitor extends Model
{
    protected $table = 'l_monitor';
    
    public function toArray(){
        {
            return[
                "SN"=>$this->sn,
                "IP"=>$this->ip,
                "登入"=>$this->u_value,
                "連線E"=>$this->v_value,
                "連線T"=>$this->w_value,
                "LP"=>$this->x_value,
                "程序"=>$this->y_value,
                "記錄時間"=>$this->m_time,
            ];      
          }
    }
}
