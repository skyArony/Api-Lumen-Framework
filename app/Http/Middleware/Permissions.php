<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\DB\api_lumen\CollectionGroup;
use App\Models\DB\api_lumen\CollectionItem;
use App\Models\DB\api_lumen\CollectionUser;
use App\Models\DB\api_lumen\GroupItem;
use App\Models\DB\api_lumen\ApiItem;
use App\Http\Controllers\ApiController;
use Tymon\JWTAuth\Facades\JWTAuth;

class Permissions
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
        // 获取该用户拥有的所有权限
        $user = JWTAuth::parseToken()->toUser()->toArray();
        $collections = CollectionUser::where('email', '=', $user['email'])->get(['collection_key'])->toArray();
        $allApi = array();
        foreach ($collections as $key1 => $value1) {
            $items = CollectionItem::where('collection_key', '=', $value1['collection_key'])->get(['item_key'])->toArray();
            foreach ($items as $key2 => $value2) {
              $allApi[] = $value2['item_key'];
            }
            $groups = CollectionGroup::where('collection_key', '=', $value1['collection_key'])->get(['group_key'])->toArray();
            foreach ($groups as $key3 => $value3) {
              $items = GroupItem::where('group_key', '=', $value3['group_key'])->get(['item_key'])->toArray();
              foreach ($items as $key4 => $value4) {
                $allApi[] = $value4['item_key'];
              }
            }
        }
        // 根据请求的url和method，确定其key
        $item = ApiItem::where('url', '=', $request->url())->where('method', '=', strtoupper($request->method()))->first(['item_key'])->toArray();
        // 检查当前请求的url是否在允许的请求表中
        if (in_array($item['item_key'], $allApi)) return $next($request);
        else {
            $apiController = new ApiController();
            return $apiController->createResponse(null, 406, -9);
        }
    }
}
