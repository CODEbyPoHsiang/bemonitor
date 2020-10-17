<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TcpConnState;

class TcpConnStateController extends Controller
{
    // public function isPrivateIP(){
    //     $ip = "103.252.215.143";
    //     $parts =  explode(".",$ip);
    //     $response = ($parts[0] === "10" || ($parts[0] === "172" && (intval($parts[1], "10") >= "16" && intval($parts[1], "10") <= "31")) || ($parts[0] === "192" && $parts[1] === "168"));

    //     return response()->json($response, 200);
    // }
    public function tcpip_info($ip){
        //導入連線數清單
        $jsonString = file_get_contents(base_path('/resources/assets/tcpConnState_rank.json'));    
        $list = json_decode($jsonString, true);
        //轉成 collect
        $collection = collect($list);
        //利用mapToGroups函數找ip
        $grouped = $collection->mapToGroups(function ($item, $key) {
            return [$item['ip'] => $item['ip']];
        });
        $grouped->toArray();
        //在json檔案中找到對應的ip
        $ip_array = $grouped->get($ip)->all();
        //將陣列轉成字串
        $ip=implode(".",$ip_array);
         $tcpconn_rank = shell_exec("/usr/local/bin/tcpConnState_rank.sh $ip");
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
    public function index()
    {
        $jsonString = file_get_contents(base_path('/resources/assets/tcpConnState_rank.json'));
    
        $data = json_decode($jsonString, true);
        $n=1;
        $i=1;
        $a=[];
        foreach ($data as $key =>$val) {
            // print_r($val["ip"]);
            // $ip = "103.252.215.143";
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
                        "is_over"=>true
                    ];
                }else{
                    $public[]=[
                        // "isPrivateIP"=>false,
                        "#"=> $i++,
                        "Public IP"=>$val["ip"],
                        "連線數"=>$val["count"],
                        "is_over"=>false
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
