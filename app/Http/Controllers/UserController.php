<?php
namespace App\Http\Controllers;

use App\Models\DB\api_lumen\User;
use App\Http\Transformers\UserTransformer;
use Illuminate\Http\Request;


class UserController extends ApiController
{
  // 返回所有的user
  public function index(Request $request)
  {
    $user = User::all();
    $userArray = $user->toArray();
    $userData = array();

    foreach ($userArray as $key => $value) {
      $array = array(
        'id' => $value['id'],
        'user_name' => $value['name'],
        'user_email' => $value['email']
      );
      $userData[] = $array;
    }
    $data = json_encode($userData);

    // return $this->response->error('This is an error.', 404);
    // return $this->response->errorNotFound();

    return $this->createResponse($data, 200, 0);
  }

  // 按ID返回user
  public function show($id)
  {
    // 这里返回的数据类型是 collection，后面的响应大部分是要以 collection 来进行转换
    $user = User::findOrFail($id);

    // 这里有诸多的响应方式，可以参考官方文档
    return $this->response->item($user, new UserTransformer);
  }


  // public function getToken(Request $request)
  // {
  //     $token = $request->input('token');

  //     dd($token);
  // }

}
