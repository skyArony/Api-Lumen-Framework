<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends ApiController
{
  /**
   * 添加一个认证中间件，因为获取token无需认证，所以把createToken除外,这里添加的话路由上就可以不用加中间件，当然想加也行
   *
   * @return void
   */
  public function __construct()
  {
      $this->middleware('auth:api', ['except' => ['createToken', 'refreshToken']]);
  }

  /**
   * 创建并获取一个token，要求附带email和password（数据来源users表）
   *
   * @param  \Illuminate\Http\Request  $request
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function createToken(Request $request)
  {
      $credentials = $request->only('email', 'password');
      if ($token = JWTAuth::attempt($credentials)) {
          return $this->respondWithToken($token);
      }
      
      return response()->json(['errmsg' => 'Unauthorized'], 401);
  }

  /**
   * 注销，把所给token加入黑名单
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function deleteToken()
  {
      Auth::logout();

      return response()->json(['message' => 'Successfully logged out']);
  }

  /**
   * 刷新token，如果开启黑名单，以前的token便会失效，指的注意的是用上面的getToken再获取一次Token并不算做刷新，两次获得的Token是并行的，即两个都可用。
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function refreshToken()
  {
      return $this->respondWithToken(JWTAuth::parseToken()->refresh());
  }

  /**
   * 将返回结果包装
   *
   * @param  string $token
   *
   * @return \Illuminate\Http\JsonResponse
   */
  protected function respondWithToken($token)
  {
      return response()->json([
          'access_token' => $token,
          'token_type' => 'bearer',
          'expires_in' => JWTAuth::factory()->getTTL() * 60,
          'issued_at'  => time()
      ]);
  }

  /**
   * 获取当前token的鉴权用户
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function me()
  {
      return response()->json(JWTAuth::user());
  }

  /**
   * Get the guard to be used during authentication.
   *
   * @return \Illuminate\Contracts\Auth\Guard
   */
  public function guard()
  {
      return JWTAuth::guard();
  }

//   // 从user获取一个token
//   public function getToken(){
//     $user = User::first();
//     $token = JWTAuth::fromUser($user);
//     return $this->respondWithToken($token);
//   }
}