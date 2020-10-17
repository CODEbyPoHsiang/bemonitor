<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lmonitor;
use Illuminate\Support\Facades\Validator;

//顯示記憶體不足，可利用這一條修改(應急用)
// ini_set('memory_limit', '1024M');

class LmonitorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    //監控紀錄清單
    public function index()
    {
        //監控記錄的資料比較龐大，必須捨棄用get()，使用cursor()
        $lmonitor = Lmonitor::orderBy('m_time', 'DESC')->take(100)->cursor()->toArray();
        $n=1;
        $lmonitor_new=[];
        foreach($lmonitor as $key => $val){
            //編號取一百筆後再給予新的編號(使用sn會是最後一百筆的編號)
            $lmonitor_no=['#'=>$n++];
            $lmonitor[$key]=$val;
            $lmonitor_new[]=array_merge($lmonitor_no,$lmonitor[$key]);
        }
        
        if (empty($lmonitor_new)) {
            $response = [
                'success' => false,
                'data' => [],
                'message' => '無任何資料'
            ];
            return response()->json($response, 202);
        }
        $response = [
            'success' => true,
            'data' => $lmonitor_new,
            'message' => '資料載入成功'
        ];
        return response()->json($response, 200);
    }
    //監控紀錄清單(分頁)
    public function paginate()
    {
        $lmonitor = Lmonitor::paginate(5)->cursor()->toArray();
        if (empty($lmonitor)) {
            $response = [
                'success' => false,
                'data' => [],
                'message' => '無任何資料'
            ];
            return response()->json($response, 202);
        }
        $response = [
            'success' => true,
            'data' => $lmonitor,
            'message' => '資料載入成功'
        ];
        return response()->json($response, 200);
    }
    //搜尋資料，使1個變數({ip})，2個參數(from、to)
    public function search_data(Request $request,$ip)
    {
        // 計算字元長度
        $leg= strlen($ip) ;
        $from = $request->from;
        $to = $request->to;
        //定義起始日期要 -1 (若無-1帶入區間，起始跟結束時間為同一天會無資料)
        $start = date("Y-m-d",strtotime($from."-1 day"));
        //計算日期區間
        $date_range = (strtotime($to)-strtotime($from))/86400;
        // dd($date_range);
        if ($leg>=3) {
            if ($date_range > 3) {
                $response = [
                'success' => false,
                'data' => [],
                'message' => "日期範圍錯誤，搜尋區間上限3天"
            ];
                return response()->json($response, 202);
            } else {
                $result = Lmonitor::where('ip','=', $ip)
            ->whereBetween('m_time', [$start, $to])
            ->cursor()
            ->toArray();
            $n=1;
            $lmonitor_new=[];
            foreach ($result as $key =>$arr) {
                //編號取100筆後再給予新的編號(使用sn會是最後20筆的編號)
                $lmonitor_no=['#'=>$n++];
                $lmonitor[$key]=$arr;
                $lmonitor_new[]=array_merge($lmonitor_no, $lmonitor[$key]);
            }
            }
            //若輸入的IP不再區間或是無資料
            if (empty($lmonitor_new)) {
                $response = [
                    'success' => false,
                    'data' => [],
                    'message' => "查無資料，請重新輸入!"
                ];
                return response()->json($response, 202);
            } else {
                $response = [
                    'success' => true,
                    'data' => $lmonitor_new,
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
    //三個參數
    // public function search($ip, $from, $to)
    // {
    //     //字串的長度
    //     $leg = strlen($ip);
    //     //定義起始日期要 -1 (若無-1帶入區間，起始跟結束時間為同一天會無資料)
    //     $start = date("Y-m-d",strtotime($from."-1 day"));
    //     $date_range = (strtotime($to)-strtotime($from))/86400;
    //     // dd($date_range);
    //     #判斷式 leg>3：至少輸入3個字元
    //     if ($leg>=3) {
    //         //判斷式 date_range > 3：日期區間大於三天會有錯誤訊息
    //         if ($date_range > 3) {
    //             $response = [
    //             'success' => false,
    //             'data' => [],
    //             'message' => "日期範圍錯誤，搜尋區間上限3天"
    //         ];
    //             return response()->json($response, 202);
    //         } else {
    //             $result = Lmonitor::where('ip', 'like', '%'.$ip.'%')
    //         ->whereBetween('m_time', [$start, $to])
    //         ->get();
    //         }
    //         if ($result->isEmpty()) {
    //             $response = [
    //                 'success' => false,
    //                 'data' => [],
    //                 'message' => "查無資料，請重新輸入!"
    //             ];
    //             return response()->json($response, 202);
    //         } else {
    //             $response = [
    //                 'success' => true,
    //                 'data' => $result,
    //                 'message' => "資料載入成功"
    //             ];
    //             return response()->json($response, 200);
    //         }
    //     } else {
    //         $response = [
    //             'success' => false,
    //             'data' => [],
    //             'message' => "請至少輸入三個字元"
    //         ];
    //         return response()->json($response, 202);
    //     }
    // }
}
