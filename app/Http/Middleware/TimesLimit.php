<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\DB\api_lumen\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\ApiController;

/* 
 *  系统：api控制系统
 *  功能：前置中间件：判断进入的请求是否还有可以用的调用次数
 * 
 */
class TimesLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // 该用户剩余可用次数大于0通过，并且减1
        $requestData = JWTAuth::parseToken()->toUser()->toArray();
        $user = User::where('email', '=', $requestData['email'])->first();
        if ($user->left_times > 0) {
            $user->left_times = $user->left_times - 1;
            $user->save();
            return $next($request);
        } else {
            $apiController = new ApiController();
            return $apiController->createResponse(null, 429, -10);
        }
    }
}
