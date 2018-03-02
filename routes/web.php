<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
  return $router->app->version();
});



// dingo 使用自有的路由器，所以你需要先获取其实例
$api = app('Dingo\Api\Routing\Router');

// 必须定义一个版本分组，“v1”来自于 .env 文件，必须填写才生效
$api->version('v1', ['namespace' => 'App\Http\Controllers'], function ($api) {
  // 认证部分：获取token
  $api->get('/auth/token', 'AuthController@createToken');
  // 认证部分：刷新token
  $api->put('/auth/token', 'AuthController@refreshToken');
  // 认证部分：删除token
  $api->delete('/auth/token', 'AuthController@deleteToken');

  // jwt-auth的路由保护，放在这里面的就需要带上token访问
  $api->group(['middleware' => ['auth', 'api.permission', 'api.timeslimit', 'api.timescounter']], function ($api) {
      // 资源获取：users
      $api->resource('/users', 'UserController');
  });

  // api item
  $api->post('/perm/item', 'PermController@itemStore');
  $api->delete('/perm/item', 'PermController@itemDestroy');
  $api->put('/perm/item', 'PermController@itemUpdate');
  $api->get('/perm/item', 'PermController@itemShow');

  // api group
  $api->post('/perm/group', 'PermController@groupStore');
  $api->delete('/perm/group', 'PermController@groupDestroy');
  $api->put('/perm/group', 'PermController@groupUpdate');
  $api->get('/perm/group', 'PermController@groupShow');

  // api collection
  $api->post('/perm/collection', 'PermController@collectionStore');
  $api->delete('/perm/collection', 'PermController@collectionDestroy');
  $api->put('/perm/collection', 'PermController@collectionUpdate');
  $api->get('/perm/collection', 'PermController@collectionShow');
  
  // 根据type，列出所有的element
  $api->get('/perm/element', 'PermController@showAllElement');
  // 差个查找

  // 联系的增删改查
  $api->post('/perm/contact', 'PermController@contactStore');
  $api->delete('/perm/contact', 'PermController@contactDestroy');
  $api->put('/perm/contact', 'PermController@contactUpdate');
  $api->get('/perm/contact', 'PermController@contactShow');

  // 设置用户的可调用次数
  $api->put('/perm/left-times', 'PermController@setLeftTimes');
  $api->put('/perm/ava-time', 'PermController@setAvatime');
});

/* 统一登录系统 */
$api->version('v1', ['namespace' => 'App\Http\Controllers\Passport'], function ($api) {
  $api->post('/password/bind', 'BindController@bindPassword');   //  密码绑定
  $api->get('/edu/grade/{term}', 'EduGradeController@getGrade');
  $api->get('/login', 'LoginController@login');  // 系统登录
  $api->post('/test', 'LoginController@test');  // 系统登录
});