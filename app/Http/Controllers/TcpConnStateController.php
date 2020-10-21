<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TcpConnState;

class TcpConnStateController extends Controller
{
    /****判斷 Private IP 的函數 */
    // public function isPrivateIP(){
    //     $ip = "103.252.215.143";
    //     $parts =  explode(".",$ip);
    //     $response = ($parts[0] === "10" || ($parts[0] === "172" && (intval($parts[1], "10") >= "16" && intval($parts[1], "10") <= "31")) || ($parts[0] === "192" && $parts[1] === "168"));
    //     return response()->json($response, 200);
    // }

    /****連線數明細記錄 */
    public function tcpip_info($ip){
        // //導入連線數清單
        // $jsonString = file_get_contents(base_path('/resources/assets/tcpConnState_rank.json'));    
        // $list = json_decode($jsonString, true);
        // //轉成 collect
        // $collection = collect($list);
        // //利用mapToGroups函數找ip
        // $grouped = $collection->mapToGroups(function ($item, $key) {
        //     return [$item['ip'] => $item['ip']];
        // });
        // $grouped->toArray();
        // // dd($a);
        // //在json檔案中找到對應的ip
        // $ip_get = $grouped->get($ip);
        // if($ip_get == null){
        //     $response = [
        //         "success"=>false,
        //         "ip"=>$ip,
        //         "data"=>[],
        //         "message"=>"查無資料"
        //     ];
        //     return response()->json($response, 202);
        // }
        // $ip_array = $ip_get->all();
        // // dd($ip_array);
        // //將陣列轉成字串
        // $ip=implode(".",$ip_array);
         $tcpconn_rank = shell_exec("/usr/local/bin/tcpConnState_rank.sh $ip");
         if($tcpconn_rank == null){
                $response = [
                    "success"=>false,
                    "ip"=>$ip,
                    "data"=>[],
                    "message"=>"查無資料"
                ];
                return response()->json($response, 202);
            }
         //轉成清單後，最後會有一個空格，所以利用trim將它去除
         //explode 以\n換行符號來做分割
         $tcpconn_rank_list=explode("\n",trim($tcpconn_rank));
         $response = [
             "success"=>true,
             "ip"=>$ip,
             "data"=>$tcpconn_rank_list,
             "message"=>"資料載入成功"
         ];
         return response()->json($response, 200);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /***顯示整體連線數清單 */
    public function index()
    {
        $jsonString = file_get_contents("/dev/shm/tcpConnState_rank.json");
    
        $data = json_decode($jsonString, true);
        $n=1;
        $i=1;
        $a=[];
        foreach ($data as $key =>$val) {
            $parts =  explode(".", $val["ip"]);
            $response = ($parts[0] === "10" || ($parts[0] === "172" && (intval($parts[1], "10") >= "16" && intval($parts[1], "10") <= "31")) || ($parts[0] === "192" && $parts[1] === "168"));
            if ($response === true) {
                $private[]=[
                    // "isPrivateIP"=>true,
                    "#"=> $n++,
                    "Private IP"=>$val["ip"],
                    "連線數"=>$val["count"],
            ];
            }
            if ($response === false) {
                if($val["count"] >= 100){
                    $public[]=[
                        // "isPrivateIP"=>false,
                        "#"=> $i++,
                        "Public IP"=>$val["ip"],
                        "連線數"=>$val["count"],
                        "is_over"=>true //true:超過100
                    ];
                }else{
                    $public[]=[
                        // "isPrivateIP"=>false,
                        "#"=> $i++,
                        "Public IP"=>$val["ip"],
                        "連線數"=>$val["count"],
                        "is_over"=>false //false 未超過
                    ];
                }
                
                };
        }
        $pr_ip = collect($private)->take(75);
        $pu_ip = collect($public)->take(75);

        $response = [
            "Private IP"=>$pr_ip,
            "Public IP"=>$pu_ip
        ];
        return response()->json($response, 200);
    }
}
