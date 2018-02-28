<?php
namespace App\Models;

use App\Libs\Snoopy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\Models\DB\passport\PassportUser;
use App\Models\DB\passport\Sessionid;

/*
 * 系统：统一登录系统
 * 功能：各个系统模拟登录的核心
 *
*/
class PassportCore extends Model
{
    // 教务系统登录，成功返回session_id
    public static function eduLogin(Request $request)
    {
        $hosturl = 'http://jwxt.xtu.edu.cn/jsxsd/';
        $yzm_url = 'http://jwxt.xtu.edu.cn/jsxsd/verifycode.servlet';
        $posturl = 'http://jwxt.xtu.edu.cn/jsxsd/xk/LoginToXk';
        $mainurl = 'http://jwxt.xtu.edu.cn/jsxsd/framework/xsMain.jsp';
        $flag_url = 'http://jwxt.xtu.edu.cn/jsxsd/xk/LoginToXk?flag=sess';

        if ($request->has('sessionid') && $request->sessionid != '') {
            $snoopy = new Snoopy;
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

                /*获取encoded的元值*/
                $flagData = array('USERNAME' => $request->sid, 'PASSWORD' => str_replace(' ', '+', $request->edupd), 'RANDOMCODE' => $yzm);
                $snoopy->submit($flag_url, $flagData);
                $flag = json_decode($snoopy->results, true)['data'];

                /*计算encoded得值*/
                $code = $request->sid . '%%%' . $request->edupd;
                $scode = explode('#', $flag)[0];
                $sxh = explode('#', $flag)[1];
                $encoded ='';
                for ($i = 0; $i < strlen($code); $i++) {
                    if ($i < 20) {
                        $encoded = $encoded.substr($code, $i, 1).substr($scode, 0, (substr($sxh, $i, 1)));
                        $scode = substr($scode, substr($sxh, $i, 1), strlen($scode));
                    } else {
                        $encoded = $encoded.substr($code, $i, strlen($code)-$i);
                        $i = strlen($code);
                    }
                }

                /* 登录 */
                $logindata = array('USERNAME' => $request->sid, 'PASSWORD' => str_replace(' ', '+', $request->edupd), 'RANDOMCODE' => $yzm, 'encoded' => $encoded); // 空格换成加号是因为base64编码在url传输中可能把加号变成空格
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
            $snoopy = new Snoopy;
            $snoopy->fetch($hosturl);
            $snoopy->setcookies();  //保存cookies
            $snoopy->fetch($yzm_url);
            $yzm_base64 = base64_encode($snoopy->results); //获取图片base64数据
            if (! $yzm = Idcode::EduIdcode($yzm_base64)) {
                return array('code' => -1);
            }

            /*获取encoded的元值*/
            $flagData = array('USERNAME' => $request->sid, 'PASSWORD' => str_replace(' ', '+', $request->edupd), 'RANDOMCODE' => $yzm);
            $snoopy->submit($flag_url, $flagData);
            $flag = json_decode($snoopy->results, true)['data'];

            /*计算encoded得值*/
            $code = $request->sid . '%%%' . $request->edupd;
            $scode = explode('#', $flag)[0];
            $sxh = explode('#', $flag)[1];
            $encoded ='';
            for ($i = 0; $i < strlen($code); $i++) {
                if ($i < 20) {
                    $encoded = $encoded.substr($code, $i, 1).substr($scode, 0, (substr($sxh, $i, 1)));
                    $scode = substr($scode, substr($sxh, $i, 1), strlen($scode));
                } else {
                    $encoded = $encoded.substr($code, $i, strlen($code)-$i);
                    $i = strlen($code);
                }
            }

            /* 登录 */
            $logindata = array('USERNAME' => $request->sid, 'PASSWORD' => str_replace(' ', '+', $request->edupd), 'RANDOMCODE' => $yzm, 'encoded' => $encoded); // 空格换成加号是因为base64编码在url传输中可能把加号变成空格
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
                    dd("$data");
                    return array('code' => -2);
                }
            }
        }
    }

    // 信息门户登录，成功返回session_id
    public static function portalLogin(Request $request)
    {
        $mainurl = 'http://202.197.224.171/zfca/login';
        $yzm_url = 'http://202.197.224.171/zfca/captcha.htm';

        // 和上面教务的sessionid复用有所不同，信息门户的有两个sessionid所以这里
        // 传进来的值实际上是一个urlencode编码的json字符串，而教务的只有一个单独的sessionid
        if ($request->has('sessionid') && $request->sessionid != '') {
            $snoopy = new Snoopy;
            $sessionInfo = json_decode(urldecode($request->sessionid), 1);
            $snoopy->cookies['JSESSIONID'] = $sessionInfo['JSESSIONID'];
            $snoopy->cookies['CASTGC'] = $sessionInfo['CASTGC'];
            $snoopy->fetch($mainurl);
            $data = $snoopy->results;
            if (preg_match('/欢迎您:/', $data)) {
                // session 有效，登记访问记录
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
                    $sessionid->portal = json_encode($snoopy->cookies);
                    $sessionid->save();
                } else {
                    $sessionid = new Sessionid;
                    $sessionid->sid = $request->sid;
                    $sessionid->portal = json_encode($snoopy->cookies);
                    $sessionid->save();
                }
                return array('code' => 0, 'cookies' => $snoopy->cookies, 'sid' => $request->sid);
            } else {
                // session 无效，进入正常登陆流程

                /* 验证码识别 &获取页面中的lt值  */
                $snoopy->fetch($mainurl);
                $snoopy->setcookies();  //保存cookies
                if ($snoopy->status != 200) {
                    return array('code' => -1);
                }
                preg_match('/<input type="hidden" name="lt" value="(.*?)" \/>/', $snoopy->results, $match);
                $lt = $match[1];
                $snoopy->fetch($yzm_url);
                $yzm_base64 = base64_encode($snoopy->results); //获取图片base64数据
                if (! $yzm = Idcode::PortalIdcode($yzm_base64)) {
                    return array('code' => -1);
                }

                /* 登录 */
                $logindata = array('username' => $request->sid, 'password' => str_replace(' ', '+', $request->portalpd), 'lt' => $lt, '_eventId' => 'submit', 'j_captcha_response' => $yzm); // 空格换成加号是因为base64编码在url传输中可能把加号变成空格
                $snoopy->submit($mainurl, $logindata);
                $data = iconv('gbk', 'utf-8', $snoopy->results);
                if ($snoopy->status != 200) {
                    // 网络故障
                    return array('code' => -1);
                }
                if (preg_match('/欢迎您:/', $data)) {
                    // session 有效，登记访问记录
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
                        $sessionid->portal = json_encode($snoopy->cookies);
                        $sessionid->save();
                    } else {
                        $sessionid = new Sessionid;
                        $sessionid->sid = $request->sid;
                        $sessionid->portal = json_encode($snoopy->cookies);
                        $sessionid->save();
                    }
                    return array('code' => 0, 'cookies' => $snoopy->cookies, 'sid' => $request->sid);
                } else {
                    if (preg_match('/用户名或密码错误。/', $data)) {
                        return array('code' => -4);
                    } elseif (preg_match('/验证码有误/', $data)) {
                        return array('code' => -3);
                    } else {
                        return array('code' => -2);
                    }
                }
            }
        } else {
            // 无sessionid，进入正常登陆流程
            /* 验证码识别 &获取页面中的lt值  */
            $snoopy = new Snoopy;
            $snoopy->fetch($mainurl);
            $snoopy->setcookies();  //保存cookies
            if ($snoopy->status != 200) {
                return array('code' => -1);
            }
            preg_match('/<input type="hidden" name="lt" value="(.*?)" \/>/', $snoopy->results, $match);
            $lt = $match[1];
            $snoopy->fetch($yzm_url);
            $yzm_base64 = base64_encode($snoopy->results); //获取图片base64数据
            if (! $yzm = Idcode::PortalIdcode($yzm_base64)) {
                return array('code' => -1);
            }

            /* 登录 */
            $logindata = array('username' => $request->sid, 'password' => str_replace(' ', '+', $request->portalpd), 'lt' => $lt, '_eventId' => 'submit', 'j_captcha_response' => $yzm); // 空格换成加号是因为base64编码在url传输中可能把加号变成空格
            $snoopy->submit($mainurl, $logindata);
            $data = iconv('gbk', 'utf-8', $snoopy->results);
            if ($snoopy->status != 200) {
                // 网络故障
                return array('code' => -1);
            }
            if (preg_match('/欢迎您:/', $data)) {
                // session 有效，登记访问记录
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
                    $sessionid->portal = json_encode($snoopy->cookies);
                    $sessionid->save();
                } else {
                    $sessionid = new Sessionid;
                    $sessionid->sid = $request->sid;
                    $sessionid->portal = json_encode($snoopy->cookies);
                    $sessionid->save();
                }
                return array('code' => 0, 'cookies' => $snoopy->cookies, 'sid' => $request->sid);
            } else {
                if (preg_match('/用户名或密码错误。/', $data)) {
                    return array('code' => -4);
                } elseif (preg_match('/验证码有误/', $data)) {
                    return array('code' => -3);
                } else {
                    return array('code' => -2);
                }
            }
        }
    }
}
