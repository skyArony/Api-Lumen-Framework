<?php
namespace App\Models;

use App\Libs\Snoopy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\Models\DB\passport\PassportUser;
use App\Models\DB\passport\Sessionid;

class PassportCore extends Model
{
    // 教务系统登录，成功返回session_id
    public static function eduLogin(Request $request)
    {
        $hosturl = 'http://jwxt.xtu.edu.cn/jsxsd/';
        $yzm_url = 'http://jwxt.xtu.edu.cn/jsxsd/verifycode.servlet';
        $posturl = 'http://jwxt.xtu.edu.cn/jsxsd/xk/LoginToXk';
        $mainurl = 'http://jwxt.xtu.edu.cn/jsxsd/framework/xsMain.jsp';
        $snoopy = new Snoopy;

        if ($request->has('sessionid') && $request->sessionid != '') {
            $snoopy->cookies['JSESSIONID'] = $request->sessionid;
            $snoopy->fetch($mainurl);
            $data = $snoopy->results;
            if (preg_match('/退出/', $data)) {
                // sessionid 有效
                // 更新ip和时间
                if ($passportUser = PassportUser::where('sid', '=', $request->sid)->first()) {
                    $passportUser->last_ip = $request->getClientIp();
                    $passportUser->last_login_at = date('Y-m-d h:i:s', time());
                    $passportUser->save();
                } else {
                    $passportUser = new PassportUser;
                    $passportUser->last_ip = $request->getClientIp();
                    $passportUser->last_login_at = date('Y-m-d h:i:s', time());
                    $passportUser->save();
                }
                // 更新sessionID
                if ($sessionid = Sessionid::where('sid', '=', $request->sid)->first()) {
                    $sessionid->edu = json_encode($snoopy->cookies);
                    $sessionid->save();
                } else {
                    $sessionid = new Sessionid;
                    $sessionid->sid = $request->sid;
                    $sessionid->edu = json_encode($snoopy->cookies);
                    $sessionid->save();
                }
                return array('code' => 0, 'cookies' => $snoopy->cookies, 'sid' => $request->sid);
            } else {
                // sessionid 无效，进入正常登陆流程
                /* 验证码识别 */
                $snoopy->fetch($hosturl);
                $snoopy->setcookies();  //保存cookies
                $snoopy->fetch($yzm_url);
                $yzm_base64 = base64_encode($snoopy->results); //获取图片base64数据
                if (! $yzm = Idcode::EduIdcode($yzm_base64)) {
                    return array('code' => -1);
                }

                /* 登录 */
                $logindata = array('USERNAME' => $request->sid, 'PASSWORD' => str_replace(' ', '+', $request->edupd), 'RANDOMCODE' => $yzm); // 空格换成加号是因为base64编码在url传输中可能把加号变成空格
                $snoopy->submit($posturl, $logindata);
                $data = $snoopy->results;
                if ($snoopy->status != 200) {
                    // 网络故障
                    return array('code' => -1);
                }
                if (preg_match('/退出/', $data)) {
                    // 更新ip和时间
                    if ($passportUser = PassportUser::where('sid', '=', $request->sid)->first()) {
                        $passportUser->last_ip = $request->getClientIp();
                        $passportUser->last_login_at = date('Y-m-d h:i:s', time());
                        $passportUser->save();
                    } else {
                        $passportUser = new PassportUser;
                        $passportUser->last_ip = $request->getClientIp();
                        $passportUser->last_login_at = date('Y-m-d h:i:s', time());
                        $passportUser->save();
                    }
                    // 更新sessionID
                    if ($sessionid = Sessionid::where('sid', '=', $request->sid)->first()) {
                        $sessionid->edu = json_encode($snoopy->cookies);
                        $sessionid->save();
                    } else {
                        $sessionid = new Sessionid;
                        $sessionid->sid = $request->sid;
                        $sessionid->edu = json_encode($snoopy->cookies);
                        $sessionid->save();
                    }
                    return array('code' => 0, 'cookies' => $snoopy->cookies, 'sid' => $request->sid);
                } elseif (preg_match('/修改密码/', $data)) {
                    if ($passportUser = PassportUser::where('sid', '=', $request->sid)->first()) {
                        $passportUser->last_ip = $request->getClientIp();
                        $passportUser->last_login_at = date('Y-m-d h:i:s', time());
                        $passportUser->save();
                    } else {
                        $passportUser = new PassportUser;
                        $passportUser->last_ip = $request->getClientIp();
                        $passportUser->last_login_at = date('Y-m-d h:i:s', time());
                        $passportUser->save();
                    }
                    if ($sessionid = Sessionid::where('sid', '=', $request->sid)->first()) {
                        $sessionid->edu = json_encode($snoopy->cookies);
                        $sessionid->save();
                    } else {
                        $sessionid = new Sessionid;
                        $sessionid->sid = $request->sid;
                        $sessionid->edu = json_encode($snoopy->cookies);
                        $sessionid->save();
                    }
                    return array('code' => 1, 'cookies' => $snoopy->cookies, 'sid' => $request->sid);
                } elseif (preg_match('/修改电子邮箱/', $data)) {
                    if ($passportUser = PassportUser::where('sid', '=', $request->sid)->first()) {
                        $passportUser->last_ip = $request->getClientIp();
                        $passportUser->last_login_at = date('Y-m-d h:i:s', time());
                        $passportUser->save();
                    } else {
                        $passportUser = new PassportUser;
                        $passportUser->last_ip = $request->getClientIp();
                        $passportUser->last_login_at = date('Y-m-d h:i:s', time());
                        $passportUser->save();
                    }
                    if ($sessionid = Sessionid::where('sid', '=', $request->sid)->first()) {
                        $sessionid->edu = json_encode($snoopy->cookies);
                        $sessionid->save();
                    } else {
                        $sessionid = new Sessionid;
                        $sessionid->sid = $request->sid;
                        $sessionid->edu = json_encode($snoopy->cookies);
                        $sessionid->save();
                    }
                    return array('code' => 2, 'cookies' => $snoopy->cookies, 'sid' => $request->sid);
                } else {
                    $data = iconv('gbk', 'utf-8', $snoopy->results);
                    if (preg_match('/用户名或密码错误/', $data)) {
                        return array('code' => -4);
                    } elseif (preg_match('/验证码错误/', $data)) {
                        return array('code' => -3);
                    } else {
                        return array('code' => -2);
                    }
                }
            }
        } else {
            // 无sessionid，进入正常登陆流程
            /* 验证码识别 */
            $snoopy->fetch($hosturl);
            $snoopy->setcookies();  //保存cookies
            $snoopy->fetch($yzm_url);
            $yzm_base64 = base64_encode($snoopy->results); //获取图片base64数据
            if (! $yzm = Idcode::EduIdcode($yzm_base64)) {
                return array('code' => -1);
            }

            /* 登录 */
            $logindata = array('USERNAME' => $request->sid, 'PASSWORD' => str_replace(' ', '+', $request->edupd), 'RANDOMCODE' => $yzm); // 空格换成加号是因为base64编码在url传输中可能把加号变成空格
            $snoopy->submit($posturl, $logindata);
            $data = $snoopy->results;
            if ($snoopy->status != 200) {
                // 网络故障
                return array('code' => -1);
            }
            if (preg_match('/退出/', $data)) {
                // 更新ip和时间
                if ($passportUser = PassportUser::where('sid', '=', $request->sid)->first()) {
                    $passportUser->last_ip = $request->getClientIp();
                    $passportUser->last_login_at = date('Y-m-d h:i:s', time());
                    $passportUser->save();
                } else {
                    $passportUser = new PassportUser;
                    $passportUser->last_ip = $request->getClientIp();
                    $passportUser->last_login_at = date('Y-m-d h:i:s', time());
                    $passportUser->save();
                }
                // 更新sessionID
                if ($sessionid = Sessionid::where('sid', '=', $request->sid)->first()) {
                    $sessionid->edu = json_encode($snoopy->cookies);
                    $sessionid->save();
                } else {
                    $sessionid = new Sessionid;
                    $sessionid->sid = $request->sid;
                    $sessionid->edu = json_encode($snoopy->cookies);
                    $sessionid->save();
                }
                return array('code' => 0, 'cookies' => $snoopy->cookies, 'sid' => $request->sid);
            } elseif (preg_match('/修改密码/', $data)) {
                if ($passportUser = PassportUser::where('sid', '=', $request->sid)->first()) {
                    $passportUser->last_ip = $request->getClientIp();
                    $passportUser->last_login_at = date('Y-m-d h:i:s', time());
                    $passportUser->save();
                } else {
                    $passportUser = new PassportUser;
                    $passportUser->last_ip = $request->getClientIp();
                    $passportUser->last_login_at = date('Y-m-d h:i:s', time());
                    $passportUser->save();
                }
                if ($sessionid = Sessionid::where('sid', '=', $request->sid)->first()) {
                    $sessionid->edu = json_encode($snoopy->cookies);
                    $sessionid->save();
                } else {
                    $sessionid = new Sessionid;
                    $sessionid->sid = $request->sid;
                    $sessionid->edu = json_encode($snoopy->cookies);
                    $sessionid->save();
                }
                return array('code' => 1, 'cookies' => $snoopy->cookies, 'sid' => $request->sid);
            } elseif (preg_match('/修改电子邮箱/', $data)) {
                if ($passportUser = PassportUser::where('sid', '=', $request->sid)->first()) {
                    $passportUser->last_ip = $request->getClientIp();
                    $passportUser->last_login_at = date('Y-m-d h:i:s', time());
                    $passportUser->save();
                } else {
                    $passportUser = new PassportUser;
                    $passportUser->last_ip = $request->getClientIp();
                    $passportUser->last_login_at = date('Y-m-d h:i:s', time());
                    $passportUser->save();
                }
                if ($sessionid = Sessionid::where('sid', '=', $request->sid)->first()) {
                    $sessionid->edu = json_encode($snoopy->cookies);
                    $sessionid->save();
                } else {
                    $sessionid = new Sessionid;
                    $sessionid->sid = $request->sid;
                    $sessionid->edu = json_encode($snoopy->cookies);
                    $sessionid->save();
                }
                return array('code' => 2, 'cookies' => $snoopy->cookies, 'sid' => $request->sid);
            } else {
                $data = iconv('gbk', 'utf-8', $snoopy->results);
                if (preg_match('/用户名或密码错误/', $data)) {
                    return array('code' => -4);
                } elseif (preg_match('/验证码错误/', $data)) {
                    return array('code' => -3);
                } else {
                    return array('code' => -2);
                }
            }
        }
    }
}
