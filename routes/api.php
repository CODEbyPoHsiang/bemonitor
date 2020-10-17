<?php

use App\Models\Bmonitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TcpConnStateController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//  return $request->user();
//});

/**** BmonitorController (table：b_monitor) ****/
// 顯示 監控機器清單
Route::get('equipment', 'BmonitorController@index');
// 顯示 監控機器分頁清單
Route::get("equipmentpage", 'BmonitorController@paginate');
// 查看單一監控機器資料
Route::get('equipment/{ip}', 'BmonitorController@show');
// 新增機器資料
Route::post('equipment', 'BmonitorController@store');
// 偵測遠端主機是否開啟 snmp服務
Route::post('snmp', 'BmonitorController@getSNMP');
// 編輯監控機器資料
Route::patch('equipment/{ip}', 'BmonitorController@update');
// 刪除(偽)機器資料 (藉由is_dell欄位變更)
Route::post('delete/{ip}', 'BmonitorController@delete');
// 還原機器資料 (藉由is_dell欄位變更)
Route::post('return/{ip}', 'BmonitorController@return');
// 顯示 資源回收桶機器資料清單
Route::get('recycle', 'BmonitorController@recycle');
// 刪除監控機器資料
// Route::delete('equipment/{ip}', 'BmonitorController@destroy');

//  監控機器總表清單_關鍵字搜尋 (使用get)
//  Route::get("search/{keyword}", [MemberApiController::class,"search"]);
Route::get("search/{keyword}", 'BmonitorController@search');
//  資源回收桶_關鍵字搜尋 (使用get)
Route::get("recycle_search/{keyword}", 'BmonitorController@recycle_search');
// // 關鍵字搜尋 (使用post)
// Route::post("search", 'BmonitorController@sea');
/********************************************************************************/
/**** TcpConnStateController (table：tcpConnState_rank.json) ****/
// 顯示 tcp連線數  (分成Public IP跟Private IP，並各取75筆資料)
Route::get("tcp_state", 'TcpConnStateController@index');
// 顯示 單一筆tcp ip連線數詳細記錄  
Route::get("tcp_ip_info/{ip}", 'TcpConnStateController@tcpip_info');
/********************************************************************************/
/**** LmonitorController (table：l_monitor) ****/
// 顯示 監控記錄清單
Route::get("monitor_list", 'LmonitorController@index');
//顯示 監控記錄 清單(分頁)
Route::get("monitor_list_page", 'LmonitorController@paginate');
//搜尋 監控記錄 清單 (3個變數)
Route::get("monitor_search/{ip}/{from}/{to}", 'LmonitorController@search');
//搜尋 監控記錄 清單 (1個變數)
Route::get("monitor_search/{ip}", 'LmonitorController@search_data');
/********************************************************************************/
/**** LeventController (table：l_event) ****/
Route::get("event_list", 'LeventController@index');
Route::get("event_search/{ip}", 'LeventController@search_data');
/********************************************************************************/
/**** TelegramSendController (table:l_event) */
Route::post("telegram_send", 'TelegramSendController@alert_message');
Route::post('ping', 'BmonitorController@getPING');
