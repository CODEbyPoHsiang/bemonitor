<?php

namespace App\Http\Controllers;

use App\Models\Bmonitor;
use FreeDSx\Snmp\SnmpClient;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Hash;


/**** 自定義分頁 (需要套件)****/
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class BmonitorController extends Controller
{
    

    /**** 自定義分頁function *****/
    public function pagination($items, $perPage = 2, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    /**** 監控機器清單列表 api ****/
    public function index()
    {
        // $bmonitor = Bmonitor::all()->toArray();
        // $bmonitor = Bmonitor::where("is_delete",'=',"1")->orderBy('create_time', 'desc')->get()->toArray();
        $bmonitor = Bmonitor::where("is_delete", '=', "1")->get()->toArray();

        $bmonitor_new = [];
        foreach ($bmonitor as $key => $arr) {
            // $type為自定義替換字串後的內容
            if ($arr["os"] == "l") {
                $type = "Linux";
            }
            if ($arr["os"] == "w") {
                $type = "Windows";
            }
            //超過閥值，定義的欄位，true超過，false未超過
            if ($arr['v_value'] >= $arr['v_threshold']) {
                $v_isOver = true;
            } else {
                $v_isOver = false;
            }
            if ($arr['w_value'] >= $arr['w_threshold']) {
                $w_isOver = true;
            } else {
                $w_isOver = false;
            }
            if ($arr['y_value'] >= $arr['y_threshold']) {
                $y_isOver = true;
            } else {
                $y_isOver = false;
            }
            // 將自定義字串先組成陣列(1)
            $bmomitor_os = ['os_name' => $type];
            // 將閥值組成陣列(2)
            $bmomitor_isOver = [
                "v_isOver" => $v_isOver,
                "w_isOver" => $w_isOver,
                "y_isOver" => $y_isOver,
            ];
            // 將其餘的值用$key = $val方式組成陣列(3)
            $bmonitor[$key] = $arr;

            //將自定義陣列跟其餘值的陣列合併
            $bmonitor_new[] = array_merge($bmomitor_os, $bmonitor[$key], $bmomitor_isOver);
        }
        if (empty($bmonitor_new)) {
            $response = [
                'success' => false,
                'data' => [],
                'message' => '無任何資料',
            ];
            return response()->json($response, 202);
        } else {
            $response = [
                'success' => true,
                'data' => $bmonitor_new,
                'message' => '資料載入成功',
            ];
            return response()->json($response, 200);
        }
    }

    /**** 監控機器清單列表 api(分頁功能) ****/
    public function paginate()
    {
        $bmonitor = Bmonitor::where("is_delete", '=', "1")->cursor()->toArray();
        $bmonitor_new = [];
        foreach ($bmonitor as $key => $arr) {
            if ($arr["os"] == "l") {
                $type = "Linux";
            }
            if ($arr["os"] == "w") {
                $type = "Windows";
            }
            $bmomitor_os = ['os_name' => $type];
            $bmonitor[$key] = $arr;
            $bmonitor_new[] = array_merge($bmomitor_os, $bmonitor[$key]);
        }
        $myCollectionObj = collect($bmonitor_new);

        // 使用 {自定義分頁的function}
        $collection = $this->pagination($myCollectionObj);

        if (empty($collection)) {
            $response = [
                'success' => false,
                'data' => [],
                'message' => '無任何資料',
            ];
            return response()->json($response, 202);
        } else {
            $response = [
                'success' => true,
                'data' => $collection,
                'message' => '資料載入成功',
            ];
            return response()->json($response, 200);
        }
    }

    /**** 偵測遠端是否開啟snmp api ****/
    public function getSNMP(Request $request)
    {
        $ip = $request->input('ip');
        $snmp = new SnmpClient([
            'host' => $ip,
            'version' => 2,
            'community' => 'cyanyellowgreen168',
        ]);
        $result = $snmp->getValue('1.3.6.1.2.1.1.5.0');
        $response = [
            'success' => true,
            'status' => 'OK',
            'message' => $result,
        ];
        return response()->json($response, 200);
    }

    /**** 偵測ip狀態 (使用ping) ****/
    public function getPING(Request $request)
    {
        $ip = $request->input('ip');
        exec("ping -n 3 -w 4 $ip", $output, $status);
        if ($status === 0) {
            $response = [
                'success' => true,
                'status' => 'OK',
                'message' => "ping 成功",
            ];
            return response()->json($response, 200);
        } else {
            $response = [
                'success' => false,
                'status' => 'FAIL',
                'message' => "ping 失敗",
            ];
            return response()->json($response, 200);
        }
    }

    /**** 新增監控機器 api ****/
    public function store(Request $request)
    {
        // 檢查IP是否重複
        $schedule = Bmonitor::where('ip', '=', strval($request->ip))->first();
        // 若無重複，則開始新增動作
        if (!$schedule) {
            // 進行新增表單驗證(驗證ip及閥值)
            $rules = [
                //填入須符合的格式及長度
                'hostname' => 'max:50',
                'ip' => 'required|ip',
                'u_threshold' => 'numeric|min:0|max:255',
                'v_threshold' => 'numeric|min:0|max:32767',
                'w_threshold' => 'numeric|min:0|max:32767',
                'x_threshold' => 'numeric|min:0|max:255',
                'y_threshold' => 'numeric|min:0|max:255',
            ];
            $messages = [
                //驗證未通過的訊息提示
                'ip.required' => 'IP為必填欄位，請重新操作',
                'hostname.max' => '主機名稱不得超過50個字符，請重新操作',
                'ip.ip' => '重新操作，請填入正確的IP格式',
                'u_threshold.numeric' => '閥值填入格式應為【數字】',
                'u_threshold.min' => '【登入者數目】閥值數設定至少為0',
                'u_threshold.max' => '【登入者數目】閥值數設定不能大於255',
                'v_threshold.numeric' => '閥值填入格式應為【數字】',
                'v_threshold.min' => '【連線數(ESTABLISHED)】閥值數設定不得為負值',
                'v_threshold.max' => '【連線數(ESTABLISHED)】閥值數設定不能大於32767',
                'w_threshold.numeric' => '閥值填入格式應為【數字】',
                'w_threshold.min' => '【連線數(TIME_WAIT)】閥值數設定不得為負值',
                'w_threshold.max' => '【連線數(TIME_WAIT)】閥值數設定不能大於32767',
                'x_threshold.numeric' => '閥值填入格式應為【數字】',
                'x_threshold.min' => '【Listen Port】閥值數設定至少為0',
                'x_threshold.max' => '【Listen Port】閥值數設定不能大於255',
                'y_threshold.numeric' => '閥值填入格式應為【數字】',
                'y_threshold.min' => '【系統程序數】閥值數設定至少為0',
                'y_threshold.max' => '【系統程序數】閥值數設定不能大於255',
            ];
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                $messages = $validator->messages();
                $errors = $messages->all();
                $response = [
                    'success' => false,
                    'data' => "Error",
                    'message' => $errors[0],
                ];
                return response()->json($response, 202);
            }
            //通過驗證後，執行新增的動作
            $bmonitor = Bmonitor::create($request->all());
            $response = [
                'success' => true,
                'data' => $bmonitor,
                'message' => '資料新增成功',
            ];
            return response()->json($response, 200);
        } else {
            //ip重複時的提示訊息
            $response = [
                'success' => false,
                'message' => '輸入的IP已存在，請重新輸入!',
                "isIpAvailable" => "no",
            ];
            return response()->json($response, 202);
        }
    }

    /**** 顯示單一筆資料 api ****/
    public function show(Bmonitor $ip)
    {
        $bmonitor = Bmonitor::where("is_delete", '=', "1")->find($ip)->toArray();

        $bmonitor_new = [];
        foreach ($bmonitor as $key => $arr) {
            if ($arr["os"] == "l") {
                $type = "Linux";
            }
            if ($arr["os"] == "w") {
                $type = "Windows";
            }

            if ($arr['v_value'] >= $arr['v_threshold']) {
                $v_isOver = true;
            } else {
                $v_isOver = false;
            }
            if ($arr['w_value'] >= $arr['w_threshold']) {
                $w_isOver = true;
            } else {
                $w_isOver = false;
            }
            if ($arr['y_value'] >= $arr['y_threshold']) {
                $y_isOver = true;
            } else {
                $y_isOver = false;
            }

            $bmomitor_os = ['os_name' => $type];
            $bmomitor_isOver = [
                "v_isOver" => $v_isOver,
                "w_isOver" => $w_isOver,
                "y_isOver" => $y_isOver,
            ];
            $bmonitor[$key] = $arr;
            $bmonitor_new[] = array_merge($bmomitor_os, $bmonitor[$key], $bmomitor_isOver);
        }
        if (empty($bmonitor)) {
            $response = [
                'success' => false,
                'data' => [],
                'message' => '查無資料，請重新操作',
            ];
            return response()->json($response, 202);
        }

        if ($bmonitor) {
            $response = [
                'success' => true,
                'data' => $bmonitor_new,
                'message' => '資料載入成功',
            ];
            return response()->json($response, 200);
        }
    }

    /**** 編輯機器資料 api ****/
    public function update(Request $request, $ip, Bmonitor $bmonitor)
    {
        $bmonitor = Bmonitor::where("is_delete", '=', "1")->find($ip);
        if (is_null($bmonitor)) {
            $response = [
                'success' => false,
                'data' => [],
                'message' => '查無此筆資料，請重新操作',
            ];
            return response()->json($response, 202);
        } else {
            //編輯時，對閥值欄位進行表單驗證
            $rules = [
                'u_threshold' => 'numeric|min:0|max:255',
                'v_threshold' => 'numeric|min:0|max:32767',
                'w_threshold' => 'numeric|min:0|max:32767',
                'x_threshold' => 'numeric|min:0|max:255',
                'y_threshold' => 'numeric|min:0|max:255',
            ];
            $messages = [
                'u_threshold.numeric' => '閥值填入格式應為【數字】',
                'u_threshold.min' => '【登入者數目】閥值數設定至少為0',
                'u_threshold.max' => '【登入者數目】閥值數設定不能大於255',
                'v_threshold.numeric' => '閥值填入格式應為【數字】',
                'v_threshold.min' => '【連線數(ESTABLISHED)】閥值數設定不得為負值',
                'v_threshold.max' => '【連線數(ESTABLISHED)】閥值數設定不能大於32767',
                'w_threshold.numeric' => '閥值填入格式應為【數字】',
                'w_threshold.min' => '【連線數(TIME_WAIT)】閥值數設定不得為負值',
                'w_threshold.max' => '【連線數(TIME_WAIT)】閥值數設定不能大於32767',
                'x_threshold.numeric' => '閥值填入格式應為【數字】',
                'x_threshold.min' => '【Listen Port】閥值數設定至少為0',
                'x_threshold.max' => '【Listen Port】閥值數設定不能大於255',
                'y_threshold.numeric' => '閥值填入格式應為【數字】',
                'y_threshold.min' => '【系統程序數】閥值數設定至少為0',
                'y_threshold.max' => '【系統程序數】閥值數設定不能大於255',
            ];
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                $messages = $validator->messages();
                $errors = $messages->all();
                $response = [
                    'success' => false,
                    'data' => "Error",
                    'message' => $errors[0],
                ];
                return response()->json($response, 202);
            }
            //通過驗證後，執行更新
            $bmonitor->update($request->all());
            return response()->json(["200" => "資料編輯成功", 'data' => $bmonitor]);
        }
    }

    /**** 刪除 api (真實刪除)****/
    public function destroy($ip)
    {
        $bmonitor = Bmonitor::destroy($ip);
        if ($bmonitor) {
            $response = [
                'success' => true,
                'message' => '資料刪除成功',
            ];
            return response()->json($response, 200);
        } else {
            $response = [
                'success' => false,
                'message' => '資料刪除失敗',
            ];
            return response()->json($response, 202);
        }
    }

    /**** 刪除的api  (變更欄位狀態，並非實際刪除) ****/
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

    /**** 還原資料的api ****/
    public function return ($ip) {
        // $bmonitor = Bmonitor::find($ip);
        $bmonitor = Bmonitor::where("is_delete", '=', "0")->find($ip);
        if (empty($bmonitor)) {
            return response()->json(['找不到資料'], 202);
        }
        $bmonitor->is_delete = 1;
        $bmonitor->save();
        return response()->json(['資料已還原'], 200);
    }

    /**** 資源回收桶資料清單 api ****/
    public function recycle()
    {
        $bmonitor = Bmonitor::where("is_delete", '=', "0")->get()->toArray();
        $bmonitor_new = [];
        foreach ($bmonitor as $key => $arr) {
            if ($arr["os"] == "l") {
                $type = "Linux";
            }
            if ($arr["os"] == "w") {
                $type = "Windows";
            }
            $bmomitor_os = ['os_name' => $type];
            $bmonitor[$key] = $arr;
            $bmonitor_new[] = array_merge($bmomitor_os, $bmonitor[$key]);
        }
        if (empty($bmonitor_new)) {
            $response = [
                'success' => false,
                'data' => [],
                'message' => '無任何資料紀錄',
            ];
            return response()->json($response, 202);
        } else {
            $response = [
                'success' => true,
                'data' => $bmonitor_new,
                'message' => '資料載入成功',
            ];
            return response()->json($response, 200);
        }
    }

    /**** 主機總表清單-搜尋 api  ****/
    public function search($keyword)
    {
        // $leg:計算字元長度
        $leg = strlen($keyword);
        // dd($leg);
        //判斷字元長度是否大於3
        if ($leg >= 3) {
            $result = Bmonitor::where("is_delete", '=', "1")->where('ip', '=', $keyword)->cursor()->toArray();
            if (empty($result)) {
                $response = [
                    'success' => false,
                    'data' => [],
                    'message' => "查無資料，請重新輸入!",
                ];
                return response()->json($response, 202);
            } else {
                $response = [
                    'success' => true,
                    'data' => $result,
                    'message' => "資料載入成功",
                ];
                return response()->json($response, 200);
            }
        } else {
            $response = [
                'success' => false,
                'data' => [],
                'message' => "請至少輸入三個字元",
            ];
            return response()->json($response, 202);
        }
    }
    /**** 資源回收桶清單-搜尋 api (get方式) ****/
    public function recycle_search($keyword)
    {
        // $leg:計算字元長度
        $leg = strlen($keyword);
        // dd($leg);
        //判斷字元長度是否大於3
        if ($leg >= 3) {
            $result = Bmonitor::where("is_delete", '=', "0")->where('ip', '=', $keyword)->cursor()->toArray();
            if (empty($result)) {
                $response = [
                    'success' => false,
                    'data' => [],
                    'message' => "查無資料，請重新輸入!",
                ];
                return response()->json($response, 202);
            } else {
                $response = [
                    'success' => true,
                    'data' => $result,
                    'message' => "資料載入成功",
                ];
                return response()->json($response, 200);
            }
        } else {
            $response = [
                'success' => false,
                'data' => [],
                'message' => "請至少輸入三個字元",
            ];
            return response()->json($response, 202);
        }
    }
}
