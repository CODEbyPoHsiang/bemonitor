<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Levent;
use Illuminate\Support\Facades\Validator;

class LeventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $levent = Levent::orderBy('e_time', 'DESC')->take(20)->cursor()->toArray();
        $n=1;
        $levent_new=[];
        foreach ($levent as $key =>$arr) {
            //將代碼修改成文字項目
            if ($arr["monitor_id"]=="v") {
                $type="連線E";
            }
            if ($arr["monitor_id"]=="w") {
                $type="連線T";
            }
            if ($arr["monitor_id"]=="y") {
                $type="程序";
            }
            if ($arr["monitor_id"]=="x") {
                $type="LP";
            }
            if ($arr["monitor_id"]=="u") {
                $type="登入";
            }
            //編號取20筆後再給予新的編號(使用sn會是最後20筆的編號)
            $levent_no=['#'=>$n++];
            $levent_name=["監控項目"=>$type];
            $levent[$key]=$arr;
            $levent_new[]=array_merge($levent_no, $levent_name, $levent[$key]);
        }
        if (empty($levent_new)) {
            $response = [
        'success' => false,
        'data' => [],
        'message' => '無任何資料'
    ];
            return response()->json($response, 202);
        }
        $response = [
    'success' => true,
    'data' => $levent_new,
    'message' => '資料載入成功'
    ];
        return response()->json($response, 200);
    }

    //搜尋資料，使1個變數({ip})，2個參數(from、to)
    public function search_data(Request $request, $ip)
    {
        //計算字元長度
        $leg = strlen($ip);
        $from = $request->from;
        $to = $request->to;
        //定義起始日期要 -1 (若無-1帶入區間，起始跟結束時間為同一天會無資料)
        $start = date("Y-m-d", strtotime($from."-1 day"));
        //計算日期區間
        $date_range = (strtotime($to)-strtotime($from))/86400;
        // dd($date_range);
        if ($leg>=3) {
            if ($date_range > 7) {
                $response = [
                'success' => false,
                'data' => [],
                'message' => "日期範圍錯誤，搜尋區間上限7天"
            ];
                return response()->json($response, 202);
            } else {
                $result = Levent::where('ip', '=', $ip)
                ->whereBetween('e_time', [$start, $to])
                ->cursor()
                ->toArray();
                //$result的陣列監控項目為代碼，要再組一次陣列加入監控項目文字名稱
                $n=1;
                $levent_new=[];
                foreach ($result as $key =>$arr) {
                    //將代碼修改成文字項目
                    if ($arr["monitor_id"]=="v") {
                        $type="連線E";
                    }
                    if ($arr["monitor_id"]=="w") {
                        $type="連線T";
                    }
                    if ($arr["monitor_id"]=="y") {
                        $type="程序";
                    }
                    if ($arr["monitor_id"]=="x") {
                        $type="LP";
                    }
                    if ($arr["monitor_id"]=="u") {
                        $type="登入";
                    }
                    //編號取20筆後再給予新的編號(使用sn會是最後20筆的編號)
                    $levent_no=['#'=>$n++];
                    $levent_name=["監控項目"=>$type];
                    $levent[$key]=$arr;
                    $levent_new[]=array_merge($levent_no, $levent_name, $levent[$key]);
                }
            }
            //若輸入的IP不再區間或是無資料
            if (empty($result)) {
                $response = [
                    'success' => false,
                    'data' => [],
                    'message' => "查無資料，請重新輸入!"
                ];
                return response()->json($response, 202);
            } else {
                $response = [
                    'success' => true,
                    'data' => $levent_new,
                    'message' => "資料載入成功"
                ];
                return response()->json($response, 200);
            }
        } else {
            $response = [
                'success' => false,
                'data' => [],
                'message' => "請至少輸入三個字元"
            ];
            return response()->json($response, 202);
        }
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
