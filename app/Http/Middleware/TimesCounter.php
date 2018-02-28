<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\DB\api_lumen\User;
use App\Models\DB\api_lumen\ApiRecord;
use App\Models\DB\api_lumen\ApiItem;
use Tymon\JWTAuth\Facades\JWTAuth;

/* 
 *  系统：api控制系统
 *  功能：后置中间件：记录api的调用信息，留作日志
 * 
 */
class TimesCounter
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $item = ApiItem::where('url', '=', $request->url())->where('method', '=', strtoupper($request->method()))->first(['item_key'])->toArray();
        // 所调用的api_key
        $apiKey = $item['item_key'];
        $requestData = JWTAuth::parseToken()->toUser()->toArray();
        $user = User::where('email', '=', $requestData['email'])->first();
        // 记录
        $apiRecord = new ApiRecord;
        $apiRecord->item_key = $apiKey;
        $apiRecord->use_time = date("Y-m-d h:i:s", time());
        $apiRecord->email = $user['email'];
        $apiRecord->errmsg = $response->original['errmsg'];
        $apiRecord->errcode = $response->original['errcode'];
        $apiRecord->status = $response->original['status'];
        $apiRecord->ip = $request->ips()[0];
        $apiRecord->host = $request->header('host');
        $apiRecord->query_string = $request->getQueryString();
        $apiRecord->save();

        return $response;
    }
}