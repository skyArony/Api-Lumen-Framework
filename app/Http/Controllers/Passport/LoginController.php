<?php

namespace App\Http\Controllers\Passport;

use Illuminate\Http\Request;
use App\Models\PassportCore;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\Passport\BindController;

/*
 *  系统：统一登录系统
 *  功能：登录
 *
 */
class LoginController extends ApiController
{
    public function login(Request $request)
    {
        // 必须要有 type 类型
        if ($request->has(['type', 'signemail', 'signpassword'])) {
            switch ($request->type) {
                case 'tel':
                    // 手机验证
                    break;
                case 'qq':
                    // 第三方：QQ
                    break;
                case 'weixin':
                    // 第三方：微信
                    break;
                case 'sid':
                    // 学号方式，先尝试信息门户portalpd，再尝试教务系统edupd
                    if ($request->has('password')) {
                        // 尝试信息门户
                        $array = PassportCore::portalCheck($request);
                        if ($array['code'] >= 0 && $array['sid'] == $request->sid) {
                            // 获取绑定状态
                            $bindStatus = BindController::getBindStatus($request->sid);
                            // 构造token并返回
                            $credentials = array('email' => $request->signemail,
                                                 'password' => $request->signpassword);
                            $customClaims = ['sid' => $request->sid, 'code' => $array['code'], 'bindStatus' => $bindStatus];
                            if ($token = JWTAuth::customClaims($customClaims)->attempt($credentials)) {
                                return response()->json([
                                    'access_token' => $token,
                                    'token_type' => 'bearer',
                                    'expires_in' => JWTAuth::factory()->getTTL() * 60,
                                    'issued_at'  => time()
                                ]);
                            }
                            return response()->json(['errmsg' => 'Unauthorized'], 401);
                        } else {
                            // 密码错误,尝试教务
                            $array = PassportCore::eduCheck($request);
                            if ($array['code'] >= 0 && $array['sid'] == $request->sid) {
                                // 获取绑定状态
                                $bindStatus = BindController::getBindStatus($request->sid);
                                // 构造token并返回
                                $credentials = array('email' => $request->signemail,
                                                     'password' => $request->signpassword);
                                $customClaims = ['sid' => $request->sid, 'code' => $array['code'], 'bindStatus' => $bindStatus];
                                if ($token = JWTAuth::customClaims($customClaims)->attempt($credentials)) {
                                    return response()->json([
                                        'access_token' => $token,
                                        'token_type' => 'bearer',
                                        'expires_in' => JWTAuth::factory()->getTTL() * 60,
                                        'issued_at'  => time()
                                    ]);
                                }
                                return response()->json(['errmsg' => 'Unauthorized'], 401);
                            } else {
                                // 密码错误
                                return $this->createResponse(null, 400, -4);
                            }
                        }
                    } else {
                        return $this->createResponse(null, 400, -65535);
                    }
                    break;
                default:
                    break;
            }
        } else {
            // 请求参数错误
            return $this->createResponse(null, 400, -65535);
        }
    }

    public function test(Request $request)
    {
        PassportCore::portalCheck($request);
    }

}
