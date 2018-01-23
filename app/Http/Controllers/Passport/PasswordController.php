<?php

namespace App\Http\Controllers\Passport;

use Illuminate\Http\Request;
use App\Models\PassportCore;
use App\Http\Controllers\ApiController;
use App\Models\DB\passport\SysEdu;
use Illuminate\Support\Facades\Crypt;

class PasswordController extends ApiController
{
    // 绑定密码
    public function bindPassword(Request $request)
    {   
        // 必须有学号这个参数才能进入下一步
        if ($request->has('sid')) {
            // 教务和信息门户必须二选一
            if ($request->has('edupd') || $request->has('portal')) {
                $bindRes = array(); // 绑定结果
                // 输入了教务密码，且教务密码正确则入库绑定，否则设置false
                if ($request->has('edupd')) {
                    $array = PassportCore::eduLogin($request);
                    if ($array['code'] >= 0 && $array['sid'] == $request->sid) {
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
                // if ($request->has('portal')) ;   // 继续完善
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
}