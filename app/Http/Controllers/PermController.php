<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DB\api_lumen\ApiCollection;
use App\Models\DB\api_lumen\ApiGroup;
use App\Models\DB\api_lumen\ApiItem;
use App\Models\DB\api_lumen\CollectionGroup;
use App\Models\DB\api_lumen\CollectionItem;
use App\Models\DB\api_lumen\CollectionUser;
use App\Models\DB\api_lumen\GroupItem;
use App\Models\DB\api_lumen\User;

class PermController extends ApiController
{
    // item:增  必需'key', 'url', 'method', 'intro'   数据创建成功一般来说顺便把刚刚创建的数据返回了
    public function itemStore(Request $request)
    {
        if ($request->has('key', 'url', 'method', 'intro')) {
            // 数据唯一性检查
            if (ApiItem::where('item_key', '=', $request->key)->first()) {
                return $this->createResponse(null, 409, -6);
            }
            // 创建新item
            $item = new ApiItem;
            $item->item_key = $request->key;
            $item->url = $request->url;
            $item->method = strtoupper($request->method);
            $item->intro = $request->intro;
            if ($item->save()) {
                $res = ApiItem::where('item_key', '=', $request->key)->first();
                $data = json_encode($res->toArray(), JSON_UNESCAPED_UNICODE);
                // 成功
                return $this->createResponse($data, 201, 0);
            } else {
                // 返回 500，未知错误
                return $this->createResponse(null, 500, -2);
            }
        } else {
            // 请求参数错误
            return $this->createResponse(null, 400, -65535);
        }
    }

    // item:删 必需key
    public function itemDestroy(Request $request)
    {
        if ($request->has('key')) {
            switch (ApiItem::where('item_key', '=', $request->key)->delete()) {
                case true:
                    return $this->createResponse(null, 200, 0);
                    break;
                case false:
                    return $this->createResponse(null, 200, 3);
                    break;
                case null:
                    return $this->createResponse(null, 500, -2);
                    break;
            }
        } else {
            return $this->createResponse(null, 400, -65535);
        }
    }

    // item:改 必需key
    public function itemUpdate(Request $request)
    {
        if ($request->has('key')) {
            if ($item = ApiItem::where('item_key', '=', $request->key)->first()) {
                if ($request->has('newkey')) $item->item_key = $request->newkey;
                if ($request->has('url')) $item->url = $request->url;
                if ($request->has('method')) $item->method = strtoupper($request->method);
                if ($request->has('intro')) $item->intro = $request->intro;
                if ($item->save()) {
                    $key = $request->has('newkey') ? $request->newkey : $request->key;
                    $res = ApiItem::where('item_key', '=', $key)->first();
                    $data = json_encode($res->toArray(), JSON_UNESCAPED_UNICODE);
                    // 成功
                    return $this->createResponse($data, 201, 0);
                } else {
                    // 返回 500，未知错误
                    return $this->createResponse(null, 500, -2);
                }
            } else {
                return $this->createResponse(null, 400, -7);
            }
            
        } else {
            return $this->createResponse(null, 400, -65535);
        }
    }

    // item:查
    public function itemShow(Request $request)
    {
        if ($request->has('key')) {
            if ($res = ApiItem::where('item_key', '=', $request->key)->first()) {
                $data = json_encode($res->toArray(), JSON_UNESCAPED_UNICODE);
                return $this->createResponse($data, 200, 0);
            } else {
                return $this->createResponse(null, 404, -8);
            }
        } else {
            return $this->createResponse(null, 400, -65535);
        }
    }

    // group:增 必须key，intro
    public function groupStore(Request $request)
    {
        if ($request->has('key', 'intro')) {
            if (ApiGroup::where('group_key', '=', $request->key)->first()) {
                return $this->createResponse(null, 409, -6);
            }
            $group = new ApiGroup;
            $group->group_key = $request->key;
            $group->intro = $request->intro;
            if ($group->save()) {
                $res = ApiGroup::where('group_key', '=', $request->key)->first();
                $data = json_encode($res->toArray(), JSON_UNESCAPED_UNICODE);
                return $this->createResponse($data, 201, 0);
            } else {
                return $this->createResponse(null, 500, -2);
            }
        } else {
            return $this->createResponse(null, 400, -65535);
        }        
    }

    // group:删  必须key
    public function groupDestroy(Request $request)
    {
        if ($request->has('key')) {
            switch (ApiGroup::where('group_key', '=', $request->key)->delete()) {
                case true:
                    return $this->createResponse(null, 200, 0);
                    break;
                case false:
                    return $this->createResponse(null, 200, 3);
                    break;
                case null:
                    return $this->createResponse(null, 500, -2);
                    break;
            }
        } else {
            return $this->createResponse(null, 400, -65535);
        }        
    }

    // group:改  必须key
    public function groupUpdate(Request $request)
    {
        if ($request->has('key')) {
            if ($group = ApiGroup::where('group_key', '=', $request->key)->first()) {
                if ($request->has('newkey')) $group->group_key = $request->newkey;
                if ($request->has('intro')) $group->intro = $request->intro;
                if ($group->save()) {
                    $key = $request->has('newkey') ? $request->newkey : $request->key;
                    $res = Apigroup::where('group_key', '=', $key)->first();
                    $data = json_encode($res->toArray(), JSON_UNESCAPED_UNICODE);
                    // 成功
                    return $this->createResponse($data, 201, 0);
                } else {
                    // 返回 500，未知错误
                    return $this->createResponse(null, 500, -2);
                }
            } else {
                return $this->createResponse(null, 400, -7);
            }
        } else {
            return $this->createResponse(null, 400, -65535);
        }        
    }

    // group:查
    public function groupShow(Request $request)
    {
        if ($request->has('key')) {
            if ($res = ApiGroup::where('group_key', '=', $request->key)->first()) {
                $data = json_encode($res->toArray(), JSON_UNESCAPED_UNICODE);
                return $this->createResponse($data, 200, 0);
            } else {
                return $this->createResponse(null, 404, -8);
            }
        } else {
            return $this->createResponse(null, 400, -65535);
        }        
    }

    // collection:增  必须 key intro 默认istemp = true
    public function collectionStore(Request $request)
    {
        if ($request->has('key', 'intro')) {
            if (ApiCollection::where('collection_key', '=', $request->key)->first()) {
                return $this->createResponse(null, 409, -6);
            }
            $collection = new ApiCollection;
            $collection->collection_key = $request->key;
            $collection->intro = $request->intro;
            if ($request->has('istemp')) $collection->istemp = $request->istemp;
            if ($collection->save()) {
                $res = ApiCollection::where('collection_key', '=', $request->key)->first();
                $data = json_encode($res->toArray(), JSON_UNESCAPED_UNICODE);
                return $this->createResponse($data, 201, 0);
            } else {
                return $this->createResponse(null, 500, -2);
            }
        } else {
            return $this->createResponse(null, 400, -65535);
        }          
    }

    // collection:删
    public function collectionDestroy(Request $request)
    {
        if ($request->has('key')) {
            switch (ApiCollection::where('collection_key', '=', $request->key)->delete()) {
                case true:
                    return $this->createResponse(null, 200, 0);
                    break;
                case false:
                    return $this->createResponse(null, 200, 3);
                    break;
                case null:
                    return $this->createResponse(null, 500, -2);
                    break;
            }
        } else {
            return $this->createResponse(null, 400, -65535);
        }           
    }

    // collection:改
    public function collectionUpdate(Request $request)
    {
        if ($request->has('key')) {
            if ($collection = ApiCollection::where('collection_key', '=', $request->key)->first()) {
                if ($request->has('newkey')) $collection->collection_key = $request->newkey;
                if ($request->has('intro')) $collection->intro = $request->intro;
                if ($request->has('istemp')) $collection->istemp = $request->istemp;
                if ($collection->save()) {
                    $key = $request->has('newkey') ? $request->newkey : $request->key;
                    $res = ApiCollection::where('collection_key', '=', $key)->first();
                    $data = json_encode($res->toArray(), JSON_UNESCAPED_UNICODE);
                    // 成功
                    return $this->createResponse($data, 201, 0);
                } else {
                    // 返回 500，未知错误
                    return $this->createResponse(null, 500, -2);
                }
            } else {
                return $this->createResponse(null, 400, -7);
            }
        } else {
            return $this->createResponse(null, 400, -65535);
        }         
    }

    // collection:查
    public function collectionShow(Request $request)
    {
        if ($request->has('key')) {
            if ($res = ApiCollection::where('collection_key', '=', $request->key)->first()) {
                $data = json_encode($res->toArray(), JSON_UNESCAPED_UNICODE);
                // 成功
                return $this->createResponse($data, 200, 0);
            } else {
                return $this->createResponse(null, 404, -8);
            }
        } else {
            return $this->createResponse(null, 400, -65535);
        }           
    } 

    // 展示所有的元素，选择type：item，GROUP，collection
    public function showAllElement(Request $request) 
    {
        if ($request->has('type')) {
            switch ($request->type) {
                case 'item':
                    if ($res = ApiItem::all()) {
                        $data = json_encode($res->toArray(), JSON_UNESCAPED_UNICODE);
                        return $this->createResponse($data, 200, 0);
                    } else {
                        return $this->createResponse(null, 404, -8);
                    }
                    break;
                case 'group':
                    if ($res = ApiGroup::all()) {
                        $data = json_encode($res->toArray(), JSON_UNESCAPED_UNICODE);
                        return $this->createResponse($data, 200, 0);
                    } else {
                        return $this->createResponse(null, 404, -8);
                    }
                    break;
                case 'collection':
                    if ($res = ApiCollection::all()) {
                        $data = json_encode($res->toArray(), JSON_UNESCAPED_UNICODE);
                        return $this->createResponse($data, 200, 0);
                    } else {
                        return $this->createResponse(null, 404, -8);
                    }
                    break;                    
                default:
                    return $this->createResponse(null, 400, -65535);
                    break;
            }
        } else {
            return $this->createResponse(null, 400, -65535);
        }            
    }

    // g-i，c-i，c-g，c-u 四种联系的插入  第一个是容器key，第二个是元素json数组，第三个是关系类型,c-u可选择开始和结束是时间，类型为timestamp
    public function contactStore(Request $request)
    {
        if ($request->has('container', 'element', 'type')) {
            switch ($request->type) {
                case 'gi':
                    if ($res = GroupItem::where('group_key', '=', $request->container)->get(['item_key'])) {
                        $data_get = $res->toArray();
                        $data_old = array();
                        foreach ($data_get as $key => $value) {
                            $data_old[] = $value['item_key'];
                        }
                    } else {
                        $data_old = array();
                    }
                    $data_new = json_decode($request->element, 1);
                    $data = array_diff(array_unique(array_merge($data_old, $data_new)), $data_old);
                    foreach ($data as $key => $value) {
                        $groupItem = new GroupItem;
                        $groupItem->group_key = $request->container;
                        $groupItem->item_key = $value;
                        $groupItem->save();
                    }
                    return $this->createResponse(null, 201, 0);
                    break;
                case 'ci':
                    if ($res = CollectionItem::where('collection_key', '=', $request->container)->get(['item_key'])) {
                        $data_get = $res->toArray();
                        $data_old = array();
                        foreach ($data_get as $key => $value) {
                            $data_old[] = $value['item_key'];
                        }
                    } else {
                        $data_old = array();
                    }
                    $data_new = json_decode($request->element, 1);
                    $data = array_diff(array_unique(array_merge($data_old, $data_new)), $data_old);
                    foreach ($data as $key => $value) {
                        $collectionItem = new CollectionItem;
                        $collectionItem->collection_key = $request->container;
                        $collectionItem->item_key = $value;
                        $collectionItem->save();
                    }
                    return $this->createResponse(null, 201, 0);
                    break;
                case 'cg':
                    if ($res = CollectionGroup::where('collection_key', '=', $request->container)->get(['group_key'])) {
                        $data_get = $res->toArray();
                        $data_old = array();
                        foreach ($data_get as $key => $value) {
                            $data_old[] = $value['group_key'];
                        }
                    } else {
                        $data_old = array();
                    }
                    $data_new = json_decode($request->element, 1);
                    $data = array_diff(array_unique(array_merge($data_old, $data_new)), $data_old);
                    foreach ($data as $key => $value) {
                        $collectionGroup = new CollectionGroup;
                        $collectionGroup->collection_key = $request->container;
                        $collectionGroup->group_key = $value;
                        $collectionGroup->save();
                    }
                    return $this->createResponse(null, 201, 0);
                    break;
                case 'cu':
                    if ($res = CollectionUser::where('collection_key', '=', $request->container)->get(['email'])) {
                        $data_get = $res->toArray();
                        $data_old = array();
                        foreach ($data_get as $key => $value) {
                            $data_old[] = $value['email'];
                        }
                    } else {
                        $data_old = array();
                    }
                    $data_new = json_decode($request->element, 1);
                    $data = array_diff(array_unique(array_merge($data_old, $data_new)), $data_old);
                    foreach ($data as $key => $value) {
                        $collectionUser = new CollectionUser;
                        $collectionUser->collection_key = $request->container;
                        $collectionUser->email = $value;
                        $collectionUser->start_at = $request->has('start') ? date("Y-m-d h:i:s", $request->start) : date("Y-m-d h:i:s",time());
                        $collectionUser->end_at = $request->has('end') ? date("Y-m-d h:i:s", $request->end) : date("Y-m-d h:i:s",time());
                        $collectionUser->save();
                    }
                    return $this->createResponse(null, 201, 0);
                    break;
                default:
                    return $this->createResponse(null, 400, -65535);
                    break;
            }
        } else {
            return $this->createResponse(null, 400, -65535);
        } 
    }

    // g-i，c-i，c-g，c-u 四种联系的删除  第一个是容器key，第二个是元素json数组，第三个是关系类型
    public function contactDestroy(Request $request)
    {
        if ($request->has('container', 'element', 'type')) {
            switch ($request->type) {
                case 'gi':
                    $data = json_decode($request->element, 1);
                    foreach ($data as $key => $value) {
                        GroupItem::where('group_key', '=', $request->container)->where('item_key', '=', $value)->delete();
                    }
                    return $this->createResponse(null, 200, 0);
                    break;
                case 'ci':
                    $data = json_decode($request->element, 1);
                    foreach ($data as $key => $value) {
                        CollectionItem::where('collection_key', '=', $request->container)->where('item_key', '=', $value)->delete();
                    }
                    return $this->createResponse(null, 200, 0);
                    break;
                case 'cg':
                    $data = json_decode($request->element, 1);
                    foreach ($data as $key => $value) {
                        CollectionGroup::where('collection_key', '=', $request->container)->where('group_key', '=', $value)->delete();
                    }
                    return $this->createResponse(null, 200, 0);
                    break;
                case 'cu':
                    $data = json_decode($request->element, 1);
                    foreach ($data as $key => $value) {
                        CollectionUser::where('collection_key', '=', $request->container)->where('email', '=', $value)->delete();
                    }
                    return $this->createResponse(null, 200, 0);
                    break;
                default:
                    return $this->createResponse(null, 400, -65535);
                    break;
            }
        } else {
            return $this->createResponse(null, 400, -65535);
        }
    }

    // c-u 联系的起始时间 ,两个元素必须
    public function contactUpdate(Request $request)
    {
        if ($request->has('collection', 'email')) {
            if ($collectionUser = CollectionUser::where('collection_key', '=', $request->collection)->where('email', '=', $request->email)->first()) {
                if ($request->has('start')) $collectionUser->start_at = date("Y-m-d h:i:s", $request->start);
                if ($request->has('end')) $collectionUser->end_at = date("Y-m-d h:i:s", $request->end);
                if ($collectionUser->save()) {
                    $res = CollectionUser::where('collection_key', '=', $request->collection)->where('email', '=', $request->email)->first();
                    $data = json_encode($res->toArray(), JSON_UNESCAPED_UNICODE);
                    // 成功
                    return $this->createResponse($data, 201, 0);
                } else {
                    // 返回 500，未知错误
                    return $this->createResponse(null, 500, -2);
                }
            } else {
                return $this->createResponse(null, 400, -7);
            }
        } else {
            return $this->createResponse(null, 400, -65535);
        }
    }

    // 列出所选择类型的容器所包含的所有元素
    public function contactShow(Request $request)
    {
        if ($request->has('container', 'type')) {
            switch ($request->type) {
                case 'gi':
                    if ($res = GroupItem::where('group_key', '=', $request->container)->get()) {
                        $data = json_encode($res->toArray(), JSON_UNESCAPED_UNICODE);
                        return $this->createResponse($data, 200, 0);
                    } else {
                        return $this->createResponse(null, 404, -8);
                    }
                    break;
                case 'ci':
                    if ($res = CollectionItem::where('collection_key', '=', $request->container)->get()) {
                        $data = json_encode($res->toArray(), JSON_UNESCAPED_UNICODE);
                        return $this->createResponse($data, 200, 0);
                    } else {
                        return $this->createResponse(null, 404, -8);
                    }
                    break;
                case 'cg':
                    if ($res = CollectionGroup::where('collection_key', '=', $request->container)->get()) {
                        $data = json_encode($res->toArray(), JSON_UNESCAPED_UNICODE);
                        return $this->createResponse($data, 200, 0);
                    } else {
                        return $this->createResponse(null, 404, -8);
                    }
                    break;
                case 'cu':
                    if ($res = CollectionUser::where('collection_key', '=', $request->container)->get()) {
                        $data = json_encode($res->toArray(), JSON_UNESCAPED_UNICODE);
                        return $this->createResponse($data, 200, 0);
                    } else {
                        return $this->createResponse(null, 404, -8);
                    }
                    break;
                default:
                    return $this->createResponse(null, 400, -65535);
                    break;
            }
        }
    }

    // 设置账户的调用次数
    public function setLeftTimes(Request $request)
    {
        if ($request->has('times', 'email')) {
            if ($user = User::where('email', '=', $request->email)->first()) {
                $user->left_times = $request->times;
                $user->save();
                return $this->createResponse(null, 201, 0);
            } else {
                return $this->createResponse(null, 400, -7);
            }
        } else {
            return $this->createResponse(null, 400, -65535);
        }
    }


   
}
