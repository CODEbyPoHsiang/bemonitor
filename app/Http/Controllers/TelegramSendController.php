<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TelegramSend;

class TelegramSendController extends Controller
{
    /****TG自動發送機器人 */
    public function telegramBot($message)
    {
        for ($i = 1; $i <= 3; $i++) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot1266084710:AAH08QnbDremKXeFIwfwXvPnDg6bWAF3dO8/sendMessage");
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $PostData = "text=$message&chat_id=-425092354";
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $PostData);
            $temp = curl_exec($ch);
            curl_close($ch);
            if (preg_match("/\"ok\"/", $temp)) {
                break;
            } else {
                sleep(1);
            }
        }
    }
    //變更傳送後的傳送欄位狀態
    public function change($ip)
    {
        $levent = TelegramSend::find($ip);
        $levent->tg_send_status = 1;
        $levent->save();
    }

    /**發送l_event事件資料表，異常發生的事件訊息到tg */
    public function alert_message(Request $request)
    {
        $levent=TelegramSend::get()->toArray();
        // print_r($string);
        // echo $string;
        // return response()->json($chunks);
            $alert = '';
        $n=0;
        foreach ($levent as $key=>$val) {
            // dd($val[$key]["monitor_id"]);
            //將代碼修改成文字項目
            if ($val["monitor_id"]=="v") {
                $type="連線E";
            }
            if ($val["monitor_id"]=="w") {
                $type="連線T";
            }
            if ($val["monitor_id"]=="y") {
                $type="程序";
            }
            if ($val["monitor_id"]=="x") {
                $type="LP";
            }
            if ($val["monitor_id"]=="u") {
                $type="登入";
            }
            // $icon ="\u{2709}";
            $ip = $val["ip"];
            $no=$n++;
            $monitor_id =$val["monitor_id"];
            $event=$val["event"];
            $time = $val["e_time"];
             $alert .= (
                "#".$no."\n".
                "ip：".$ip. "\n".
                "事件類型："."異常"."\n".
                "監控項目：" .$type."\n".
                "事件內容：".$event. "\n".
                "告警時間：".$time. "\n".
                '--------------------------------'."\n"
            );
            // return $this->telegramBot($alert);



            //變更tg傳送狀態，使用上一個"change"function
            // $this->change($ip);
        }

        // print_r($alert);
        // 傳送告警內容，使用自動轉發機器人function，使用自動轉發機器人function
        $icon ="\u{2709}";
        $a= (
            $icon.  "【事件告警】". "\n".  "\n".
                $alert. "\n"
        );
        // print_r($a);
        return $this->telegramBot($alert);
    }
    


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
