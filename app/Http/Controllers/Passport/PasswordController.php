<?php

namespace App\Http\Controllers\Passport;

use Illuminate\Http\Request;
use App\Models\PassportCore;
use App\Http\Controllers\ApiController;
use App\Models\DB\passport\SysEdu;
use App\Models\DB\passport\SysPortal;
use App\Models\DB\passport\PassportUser;
use Illuminate\Support\Facades\Crypt;

/* 
 *  系统：统一登录系统
 *  功能：绑定通行证
 * 
 */
class PasswordController extends ApiController
{
    // 绑定密码
    public function bindPassword(Request $request)
    {   
        // 必须有学号这个参数才能进入下一步
        if ($request->has('sid')) {
            // 教务和信息门户必须二选一
            if ($request->has('edupd') || $request->has('portalpd')) {
                $bindRes = array(); // 绑定结果
                // 输入了教务密码，且教务密码正确则入库绑定，否则设置false
                if ($request->has('edupd')) {
                    $array = PassportCore::eduLogin($request);
                    if ($array['code'] >= 0 && $array['sid'] == $request->sid) {
                        // 修改bind_status字段
                        $passportUser = PassportUser::where('sid', '=', $request->sid)->first();
                        $bindStatus = explode("#", $passportUser->bind_status);
                        $bindStatus[1] = '1';   // 索引1是代表教务
                        $bind_status = '';
                        foreach ($bindStatus as $key => $value) {
                            $bind_status .= $value.'#';
                        }
                        $passportUser->bind_status = $bind_status;
                        $passportUser->save();
                        // 存储绑定数据
                        if ($sysEdu = SysEdu::where('sid', '=', $request->sid)->first()) {
                            $sysEdu->password = Crypt::encrypt($request->edupd);  // 加密
                            $sysEdu->save();
                        } else {
                            $sysEdu = new SysEdu;
                            $sysEdu->sid = $request->sid;
                            $sysEdu->password = Crypt::encrypt($request->edupd);  // 加密
                            $sysEdu->save();
                        }
                        $bindRes['edu']['status'] = true;
                        $bindRes['edu']['code'] = $array['code'];
                    } else {
                        $bindRes['edu']['status'] = false;
                        $bindRes['edu']['code'] = $array['code'];
                    }
                }
                // 信息门户密码的绑定
                if ($request->has('portalpd')) {
                    $array = PassportCore::portalLogin($request);
                    if ($array['code'] >= 0 && $array['sid'] == $request->sid) {
                        // 修改bind_status字段
                        $passportUser = PassportUser::where('sid', '=', $request->sid)->first();
                        $bindStatus = explode("#", $passportUser->bind_status);
                        $bindStatus[3] = '1';   // 索引1是代表信息门户
                        $bind_status = '';
                        foreach ($bindStatus as $key => $value) {
                            $bind_status .= $value.'#';
                        }
                        $passportUser->bind_status = substr($bind_status, 0, -2);
                        $passportUser->save();
                        // 存储绑定数据
                        if ($SysPortal = SysPortal::where('sid', '=', $request->sid)->first()) {
                            $SysPortal->password = Crypt::encrypt($request->portalpd);  // 加密
                            $SysPortal->save();
                        } else {
                            $SysPortal = new SysPortal;
                            $SysPortal->sid = $request->sid;
                            $SysPortal->password = Crypt::encrypt($request->portalpd);  // 加密
                            $SysPortal->save();
                        }
                        $bindRes['portal']['status'] = true;
                        $bindRes['portal']['code'] = $array['code'];
                    } else {
                        $bindRes['portal']['status'] = false;
                        $bindRes['portal']['code'] = $array['code'];
                    }
                }
                // if ($request->has('library')) ;   // 继续完善
                $data = json_encode($bindRes);
                return $this->createResponse($data, 200, 0);
            } else {
                // 必需参数不足，导致错误
                return $this->createResponse(null, 400, -65535);
            }
        } else {
            return $this->createResponse(null, 400, -65535);
        }
    }

    // 获取各系统的绑定状态，这个只有在用户登录或者主动调用这个方法的时候会被触发
    public static function getBindStatus($sid)
    {
        $passportUser = PassportUser::where('sid', '=', $sid)->first();
        if ($passportUser->bind_status == '') {
            $passportUser->bind_status = '0#0#0#0#0#0#0';   // 这里之所以采用这种形式是因为，各系统的绑定状态会编码在登录成功返回的token中发给用户，如果采用json的话，生成token太长。但也有个缺点就是如果以后添加了新的绑定系统，就需要修改这个默认字符串、数据库字段注释、文档三个东西
            $passportUser->save();
            return $passportUser->bind_status;
        } else {
            return $passportUser->bind_status;
        }
    }

}