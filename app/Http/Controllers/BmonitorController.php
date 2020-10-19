<?php

namespace App\Http\Controllers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Bmonitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use FreeDSx\Snmp\SnmpClient;



// 自訂義分頁
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class BmonitorController extends Controller
{
    //自訂義分頁function
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    public function pagination($items, $perPage = 2, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    //清單列表 api
    public function index()
    {
        // $bmonitor = Bmonitor::all()->toArray();
        // $bmonitor = Bmonitor::where("is_delete",'=',"1")->orderBy('create_time', 'desc')->get()->toArray();
        $bmonitor = Bmonitor::where("is_delete", '=', "1")->get()->toArray();
        
        $bmonitor_new=[];
        foreach ($bmonitor as $key=>$arr) {
            // 將要變更的欄位的value值用"if else"寫出每個可能
            // $type為自定義想要的字串內容
            if ($arr["os"]=="l") {
                $type="Linux";
            }
            if ($arr["os"]=="w") {
                $type="Windows";
            }
            //超過閥值，定義的欄位，true超過，false未超過
            if ($arr['v_value']>=$arr['v_threshold']) {
                $v_isOver = true;
            } else {
                $v_isOver= false;
            }
            if ($arr['w_value']>=$arr['w_threshold']) {
                $w_isOver= true;
            } else {
                $w_isOver=false;
            }
            if ($arr['y_value']>=$arr['y_threshold']) {
                $y_isOver= true;
            } else {
                $y_isOver=false;
            }
            
            // 將自定義字串先組成陣列(1)
            $bmomitor_os=['os_name'=>$type];
            // 將閥值組成陣列(2)
            $bmomitor_isOver = [
                "v_isOver"=>$v_isOver,
                "w_isOver"=>$w_isOver,
                "y_isOver"=>$y_isOver,
            ];
            // 將其餘的值用$key = $val方式組成陣列(3)
            $bmonitor[$key]=$arr;
            
            //將自定義陣列跟其餘值的陣列合併
            $bmonitor_new[]= array_merge($bmomitor_os, $bmonitor[$key], $bmomitor_isOver);
        }
        // dd($bmomitor_r);
        if (empty($bmonitor_new)) {
            $response = [
                'success' => false,
                'data' =>[] ,
                'message' => '無任何資料'
            ];
            return response()->json($response, 202);
        } else {
            $response = [
                'success' => true,
                'data' => $bmonitor_new,
                'message' => '資料載入成功'
            ];
            return response()->json($response, 200);
        }
    }

    //分頁清單列表 api
    public function paginate()
    {
        $bmonitor = Bmonitor::where("is_delete", '=', "1")->cursor()->toArray();
        // dd($bmonitor);
        $bmonitor_new=[];
        foreach ($bmonitor as $key=>$arr) {
            if ($arr["os"]=="l") {
                $type="Linux";
            }
            if ($arr["os"]=="w") {
                $type="Windows";
            }
            $bmomitor_os=['os_name'=>$type];
            $bmonitor[$key]=$arr;
            $bmonitor_new[]= array_merge($bmomitor_os, $bmonitor[$key]);
        }

        $myCollectionObj = collect($bmonitor_new);
        
        // 使用 {自訂義分頁的function}
        $collection = $this->pagination($myCollectionObj);
   
        // return response()->json($collection,200 );

        if (empty($collection)) {
            $response = [
                'success' => false,
                'data' => [],
                'message' => '無任何資料'
            ];
            return response()->json($response, 202);
        } else {
            $response = [
                'success' => true,
                'data' => $collection,
                'message' => '資料載入成功'
            ];
            return response()->json($response, 200);
        }
    }
    //偵測遠端是否開啟snmp api
    public function getSNMP(Request $request)
    {
        $ip = $request->input('ip'); // 接收前端丟過來的post的ip值
        $snmp = new SnmpClient([
            'host' => $ip,
            'version' => 2,
            'community' => 'cyanyellowgreen168',
        ]);
        $result = $snmp->getValue('1.3.6.1.2.1.1.5.0');
        $response  = [
            'success' => true,
            'status' => 'OK',
            'message' => $result,
        ];
        return response()->json($response, 200);
    }
    //偵測ip狀態 (使用ping)
    public function getPING(Request $request)
    {
        $ip = $request->input('ip');
        exec("ping -n 3 -w 4 $ip", $output, $status);
        if ($status === 0) {
            $response  = [
                'success' => true,
                'status' => 'OK',
                'message' => "ping 成功",
            ];
            return response()->json($response, 200);
        } else {
            $response  = [
                'success' => false,
                'status' => 'FAIL',
                'message' => "ping 失敗",
            ];
            return response()->json($response, 200);
        }
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // 新增 api
    public function store(Request $request)
    {
        // 檢查IP是否重複
        $schedule = Bmonitor::where('ip', '=', strval($request->ip))->first();
        // 若無重複，則開始新增動作
        if (!$schedule) {
            // 進行新增表單驗證(ip及閥值)
            $rules=[
                //填入須符合的格式及長度
            'hostname' => 'max:50',
            'ip'=>'required|ip',
            'u_threshold' => 'numeric|min:0|max:255',
            'v_threshold' => 'numeric|min:0|max:32767',
            'w_threshold' => 'numeric|min:0|max:32767',
            'x_threshold' => 'numeric|min:0|max:255',
            'y_threshold' => 'numeric|min:0|max:255'
        ];
            $messages=[
                //驗證未通過的訊息提示
        'ip.required' => 'IP為必填欄位，請重新操作',
        'hostname.max' => '主機名稱不得超過50個字符，請重新操作',
        'ip.ip' => '重新操作，請填入正確的IP格式',
        'u_threshold.numeric' => '閥值填入格式應為【數字】',
        'u_threshold.min' => '【登入者數目】閥值數設定至少為0',
        'u_threshold.max' => '【登入者數目】閥值數設定不能大於255',
        'v_threshold.numeric'=> '閥值填入格式應為【數字】',
        'v_threshold.min' => '【連線數(ESTABLISHED)】閥值數設定不得為負值',
        'v_threshold.max' => '【連線數(ESTABLISHED)】閥值數設定不能大於32767',
        'w_threshold.numeric'=> '閥值填入格式應為【數字】',
        'w_threshold.min' => '【連線數(TIME_WAIT)】閥值數設定不得為負值',
        'w_threshold.max' => '【連線數(TIME_WAIT)】閥值數設定不能大於32767',
        'x_threshold.numeric'=> '閥值填入格式應為【數字】',
        'x_threshold.min' => '【Listen Port】閥值數設定至少為0',
        'x_threshold.max' => '【Listen Port】閥值數設定不能大於255',
        'y_threshold.numeric'=> '閥值填入格式應為【數字】',
        'y_threshold.min' => '【系統程序數】閥值數設定至少為0',
        'y_threshold.max' => '【系統程序數】閥值數設定不能大於255',
        ];
            $validator=Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                $messages=$validator->messages();
                $errors=$messages->all();
                $response =[
                'success' => false,
                'data' => "Error",
                'message' => $errors[0]
            ];
                return response()->json($response, 202);
            }
            //通過驗證後，執行新增的動作
            $bmonitor = Bmonitor::create($request->all());
            // $bmonitor = new Bmonitor;
            // $bmonitor->hostname = strval($request->monitor);
            // $bmonitor->ip = strval($request->ip);
            // $bmonitor->mac = strval($request->operator);
            // $bmonitor->os = strval($request->os);
            // $bmonitor->main_group = strval($request->main_group);
            // $bmonitor->sub_group = strval($request->sub_group);
            // $bmonitor->community = strval($request->community);
            // $bmonitor->monitor = strval($request->monitor);
            // $bmonitor->u_monitor = strval($request->u_monitor);
            // $bmonitor->v_monitor = strval($request->v_monitor);
            // $bmonitor->w_monitor = strval($request->w_monitor);
            // $bmonitor->x_monitor = strval($request->x_monitor);
            // $bmonitor->y_monitor = strval($request->y_monitor);
            // $bmonitor->u_threshold = strval($request->u_threshold);
            // $bmonitor->v_threshold = strval($request->v_threshold);
            // $bmonitor->w_threshold = strval($request->w_threshold);
            // $bmonitor->x_threshold = strval($request->x_threshold);
            // $bmonitor->y_threshold = strval($request->y_threshold);
            // $bmonitor->alert = strval($request->alert);
            // $bmonitor->u_alert = strval($request->u_alert);
            // $bmonitor->v_alert = strval($request->v_alert);
            // $bmonitor->w_alert = strval($request->w_alert);
            // $bmonitor->x_alert = strval($request->x_alert);
            // $bmonitor->y_alert = strval($request->y_alert);
            // $bmonitor->u_notice = strval($request->u_notice);
            // $bmonitor->v_notice = strval($request->v_notice);
            // $bmonitor->w_notice = strval($request->w_notice);
            // $bmonitor->x_notice = strval($request->x_notice);
            // $bmonitor->y_notice = strval($request->y_notice);
            // $bmonitor->u_value = strval($request->u_value);
            // $bmonitor->v_value = strval($request->v_value);
            // $bmonitor->w_value = strval($request->w_value);
            // $bmonitor->x_value = strval($request->x_value);
            // $bmonitor->y_value = strval($request->y_value);
            // $bmonitor->note = strval($request->note);
            // $bmonitor->save();
            $response = [
            'success' => true,
            'data' => $bmonitor,
            'message' => '資料新增成功'
        ];
            return response()->json($response, 200);
        } else {
            //ip重複時的提示
            $response = [
            'success' => false,
            'message' => '輸入的IP已存在，請重新輸入!',
            "isIpAvailable" => "no"
        ];
            return response()->json($response, 202);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $ip
     * @return \Illuminate\Http\Response
     */
    // 單一筆資料 api
    public function show(Bmonitor $ip)
    {
        $bmonitor = Bmonitor::where("is_delete", '=', "1")->find($ip)->toArray();
        
        $bmonitor_new=[];

        foreach ($bmonitor as $key=>$arr) {
            if ($arr["os"]=="l") {
                $type="Linux";
            }
            if ($arr["os"]=="w") {
                $type="Windows";
            }

            if ($arr['v_value']>=$arr['v_threshold']) {
                $v_isOver = true;
            } else {
                $v_isOver= false;
            }
            if ($arr['w_value']>=$arr['w_threshold']) {
                $w_isOver= true;
            } else {
                $w_isOver=false;
            }
            if ($arr['y_value']>=$arr['y_threshold']) {
                $y_isOver= true;
            } else {
                $y_isOver=false;
            }
                
            $bmomitor_os=['os_name'=>$type];
            $bmomitor_isOver = [
                "v_isOver"=>$v_isOver,
                "w_isOver"=>$w_isOver,
                "y_isOver"=>$y_isOver,
            ];
            $bmonitor[$key]=$arr;
            $bmonitor_new[]= array_merge($bmomitor_os, $bmonitor[$key], $bmomitor_isOver);
        }
        if (empty($bmonitor)) {
            $response = [
                'success' => false,
                'data' => [],
                'message' => '查無資料，請重新操作'
            ];
            return response()->json($response, 202);
        }
        

        if ($bmonitor) {
            $response = [
                'success' => true,
                'data' => $bmonitor_new,
                'message' => '資料載入成功'
            ];
            return response()->json($response, 200);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Bmonitor  $bmonitor
     * @return \Illuminate\Http\Response
     */
    // 編輯/更新 api
    public function update(Request $request, $ip, Bmonitor $bmonitor)
    {
        $bmonitor = Bmonitor::where("is_delete", '=', "1")->find($ip);
        // dd($bmonitor);
        if (is_null($bmonitor)) {
            $response = [
                'success' => false,
                'data' => [],
                'message' => '查無此筆資料，請重新操作'
            ];
            return response()->json($response, 202);
        } else {
            //編輯時，對閥值欄位進行表單驗證
            $rules=[
            'u_threshold' => 'numeric|min:0|max:255',
            'v_threshold' => 'numeric|min:0|max:32767',
            'w_threshold' => 'numeric|min:0|max:32767',
            'x_threshold' => 'numeric|min:0|max:255',
            'y_threshold' => 'numeric|min:0|max:255'
        ];
            $messages=[
        'u_threshold.numeric' => '閥值填入格式應為【數字】',
        'u_threshold.min' => '【登入者數目】閥值數設定至少為0',
        'u_threshold.max' => '【登入者數目】閥值數設定不能大於255',
        'v_threshold.numeric'=> '閥值填入格式應為【數字】',
        'v_threshold.min' => '【連線數(ESTABLISHED)】閥值數設定不得為負值',
        'v_threshold.max' => '【連線數(ESTABLISHED)】閥值數設定不能大於32767',
        'w_threshold.numeric'=> '閥值填入格式應為【數字】',
        'w_threshold.min' => '【連線數(TIME_WAIT)】閥值數設定不得為負值',
        'w_threshold.max' => '【連線數(TIME_WAIT)】閥值數設定不能大於32767',
        'x_threshold.numeric'=> '閥值填入格式應為【數字】',
        'x_threshold.min' => '【Listen Port】閥值數設定至少為0',
        'x_threshold.max' => '【Listen Port】閥值數設定不能大於255',
        'y_threshold.numeric'=> '閥值填入格式應為【數字】',
        'y_threshold.min' => '【系統程序數】閥值數設定至少為0',
        'y_threshold.max' => '【系統程序數】閥值數設定不能大於255',
        ];
            $validator=Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                $messages=$validator->messages();
                $errors=$messages->all();
                $response =[
                'success' => false,
                'data' => "Error",
                'message' => $errors[0]
            ];
                return response()->json($response, 202);
            }
            $bmonitor->update($request->all());
            return response()->json(["200" => "資料編輯成功", 'data' => $bmonitor]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Bmonitor  $bmonitor
     * @return \Illuminate\Http\Response
     */
    // 刪除 api
    public function destroy($ip)
    {
        $bmonitor = Bmonitor::destroy($ip);
        if ($bmonitor) {
            $response = [
                'success' => true,
                'message' => '資料刪除成功'
            ];
            return response()->json($response, 200);
        } else {
            $response = [
                'success' => false,
                'message' => '資料刪除失敗'
            ];
            return response()->json($response, 202);
        }
    }

    //刪除的api (只是把欄位變更狀態，並非真的刪除)
    public function delete($ip)
    {
        // $bmonitor = Bmonitor::find($ip);
        $bmonitor = Bmonitor::where("is_delete", '=', "1")->find($ip);
        if (empty($bmonitor)) {
            return response()->json(['刪除資料失敗'], 202);
        }
        $bmonitor->is_delete = 0;
        $bmonitor->monitor = 0;
        $bmonitor->save();
        return response()->json(['刪除資料成功'], 200);
    }
    //還原資料的api
    public function return($ip)
    {
        // $bmonitor = Bmonitor::find($ip);
        $bmonitor = Bmonitor::where("is_delete", '=', "0")->find($ip);
        if (empty($bmonitor)) {
            return response()->json(['找不到資料'], 202);
        }
        $bmonitor->is_delete = 1;
        $bmonitor->save();
        return response()->json(['資料已還原'], 200);
    }

    //資源回收桶 api
    public function recycle()
    {
        $bmonitor = Bmonitor::where("is_delete", '=', "0")->get()->toArray();
        $bmonitor_new=[];
        foreach ($bmonitor as $key=>$arr) {
            if ($arr["os"]=="l") {
                $type="Linux";
            }
            if ($arr["os"]=="w") {
                $type="Windows";
            }
            $bmomitor_os=['os_name'=>$type];
            $bmonitor[$key]=$arr;
            $bmonitor_new[]= array_merge($bmomitor_os, $bmonitor[$key]);
        }
        if (empty($bmonitor_new)) {
            $response = [
                'success' => false,
                'data' => [],
                'message' => '無任何資料紀錄'
            ];
            return response()->json($response, 202);
        } else {
            $response = [
                'success' => true,
                'data' => $bmonitor_new,
                'message' => '資料載入成功'
            ];
            return response()->json($response, 200);
        }
    }

    // 主機總表清單-搜尋 api (get方式)
    public function search($keyword)
    {
        // $leg:計算字元長度
        $leg= strlen($keyword) ;
        // dd($leg);
        //判斷字元長度是否大於3
        if ($leg>=3) {
            //判斷搜尋的ip正確性(利用正規表示)
            $prge_ip = preg_match('/^(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}$/', $keyword);
            if ($prge_ip == 0) {
                $response = [
                'success' => false,
                'data' => [],
                'message' => "IP格式不正確，請重新輸入!"
            ];
                return response()->json($response, 202);
            }
            //以上2個判斷式均通過後，即執行搜尋動作
            $result = Bmonitor::where("is_delete", '=', "1")->where('ip', '=', $keyword)->cursor()->toArray();
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
                    'data' => $result,
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

    // 資源回收桶清單-搜尋 api (get方式)
    public function recycle_search($keyword)
    {
        // $leg:計算字元長度
        $leg= strlen($keyword) ;
        // dd($leg);
        //判斷字元長度是否大於3
        if ($leg>=3) {
            //判斷搜尋的ip正確性(利用正規表示)
            $prge_ip = preg_match('/^(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}$/', $keyword);
            if ($prge_ip == 0) {
                $response = [
                'success' => false,
                'data' => [],
                'message' => "IP格式不正確，請重新輸入!"
            ];
                return response()->json($response, 202);
            }
            //以上2個判斷式均通過後，即執行搜尋動作
            $result = Bmonitor::where("is_delete", '=', "0")->where('ip', '=', $keyword)->cursor()->toArray();
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
                    'data' => $result,
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
}
