<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\DB\api_lumen\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\ApiController;


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
