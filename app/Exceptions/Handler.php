<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\QueryException;
use FreeDSx\Snmp\Exception\ConnectionException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
class Handler extends ExceptionHandler
{
    
    

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];
//     public function render($request, Exception $exception)
// {
//     if ($exception instanceof QueryException)) {
//         return response()->view('errors.query-exception', [], 500);
//     }

//     return parent::render($request, $exception);
// }
    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        //GET單筆資料時，報錯誤
        $this->renderable(function (NotFoundHttpException $e, $request) {
            return response()->json(  $response = [
                'success' => false,
                'data' => [],
                'message' => '資料不存在，請重新操作!'], 202);
        });
        // 資料庫錯誤報錯
        $this->renderable(function (QueryException $e, $request) {
            return response()->json(  $response = [
                'success' => false,
                'message' => '操作錯誤，請重新操作'], 202);
        });
        // snmp連接失敗報錯誤
        $this->renderable(function (ConnectionException $e, $request) {
            return response()->json(  $response = [
                'success' => false,
                'status' =>"FAIL",
                'message' => '您所輸入的IP可能尚未開啟SNMP服務，請確認後再操作'], 202);
        });
        // token 未帶報錯誤
        $this->renderable(function (RouteNotFoundException $e, $request) {
            return response()->json(  $response = [
                'success' => false,
                'status' =>"FAIL",
                'message' => '未經允許登入，請重新操作'], 202);

                
        });
        

    }
    


}
