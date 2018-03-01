<?php
namespace App\Models;

use App\Libs\Snoopy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DB\passport\SysEdu;
use App\Models\DB\passport\SysPortal;
use Illuminate\Database\Eloquent\Model;
use App\Models\DB\passport\PassportUser;
use App\Models\DB\passport\Sessionid;
use Illuminate\Support\Facades\Crypt;

/*
 * 系统：统一登录系统
 * 功能：各个系统模拟登录的核心
 * login：用于登录系统，然后进行下一步操作，先session然后账号+密码，目的是为了进入系统
 * check：用于验证账号和密码，不用session，仅用账号+密码进行尝试，目的是为了验证
 *
*/
class PassportCore extends Model
{

    // 验证教务账号密码，仅用账号+密码进行尝试。用于绑定和登录，成功更新库中session code>=0 为成功登录
    public static function eduCheck(Request $request)
    {
        $hosturl = 'http://jwxt.xtu.edu.cn/jsxsd/';
        $yzm_url = 'http://jwxt.xtu.edu.cn/jsxsd/verifycode.servlet';
        $posturl = 'http://jwxt.xtu.edu.cn/jsxsd/xk/LoginToXk';
        $mainurl = 'http://jwxt.xtu.edu.cn/jsxsd/framework/xsMain.jsp';
        $flag_url = 'http://jwxt.xtu.edu.cn/jsxsd/xk/LoginToXk?flag=sess';

        $snoopy = new Snoopy;

        /* 验证码识别 */
        $snoopy->fetch($hosturl);
        $snoopy->setcookies();  //保存cookies
        $snoopy->fetch($yzm_url);
        $yzm_base64 = base64_encode($snoopy->results); //获取图片base64数据
        if (! $yzm = Idcode::EduIdcode($yzm_base64)) {
            return array('code' => -1);
        }

        /*获取encoded的元值*/
        $flagData = array('USERNAME' => $request->sid, 'PASSWORD' => str_replace(' ', '+', $request->password), 'RANDOMCODE' => $yzm);
        $snoopy->submit($flag_url, $flagData);
        $flag = json_decode($snoopy->results, true)['data'];

        /*计算encoded得值*/
        $code = $request->sid . '%%%' . $request->password;
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
        $logindata = array('USERNAME' => $request->sid, 'PASSWORD' => str_replace(' ', '+', $request->password), 'RANDOMCODE' => $yzm, 'encoded' => $encoded); // 空格换成加号是因为base64编码在url传输中可能把加号变成空格
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
                $passportUser->save();
            } else {
                $passportUser = new PassportUser;
                $passportUser->last_ip = $request->getClientIp();
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
            // 存储绑定数据
            if ($sysEdu = SysEdu::where('sid', '=', $request->sid)->first()) {
                $sysEdu->password = Crypt::encrypt($request->password);  // 加密
                $sysEdu->save();
            } else {
                $sysEdu = new SysEdu;
                $sysEdu->sid = $request->sid;
                $sysEdu->password = Crypt::encrypt($request->password);  // 加密
                $sysEdu->save();
            }
            return array('code' => 0, 'sid' => $request->sid);
        } elseif (preg_match('/修改密码/', $data)) {
            // 更新ip和时间
            if ($passportUser = PassportUser::where('sid', '=', $request->sid)->first()) {
                $passportUser->last_ip = $request->getClientIp();
                $passportUser->save();
            } else {
                $passportUser = new PassportUser;
                $passportUser->last_ip = $request->getClientIp();
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
            // 存储绑定数据
            if ($sysEdu = SysEdu::where('sid', '=', $request->sid)->first()) {
                $sysEdu->password = Crypt::encrypt($request->password);  // 加密
                $sysEdu->save();
            } else {
                $sysEdu = new SysEdu;
                $sysEdu->sid = $request->sid;
                $sysEdu->password = Crypt::encrypt($request->password);  // 加密
                $sysEdu->save();
            }
            return array('code' => 1, 'sid' => $request->sid);
        } elseif (preg_match('/修改电子邮箱/', $data)) {
            // 更新ip和时间
            if ($passportUser = PassportUser::where('sid', '=', $request->sid)->first()) {
                $passportUser->last_ip = $request->getClientIp();
                $passportUser->save();
            } else {
                $passportUser = new PassportUser;
                $passportUser->last_ip = $request->getClientIp();
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
            // 存储绑定数据
            if ($sysEdu = SysEdu::where('sid', '=', $request->sid)->first()) {
                $sysEdu->password = Crypt::encrypt($request->password);  // 加密
                $sysEdu->save();
            } else {
                $sysEdu = new SysEdu;
                $sysEdu->sid = $request->sid;
                $sysEdu->password = Crypt::encrypt($request->password);  // 加密
                $sysEdu->save();
            }
            return array('code' => 2, 'sid' => $request->sid);
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

    // 验证信息门户的账号和密码，仅用账号+密码进行尝试。用于绑定和登录，成功更新库中session code>=0 为成功登录
    public static function portalCheck(Request $request)
    {
        $mainurl = 'http://202.197.224.171/zfca/login?service=http%3A%2F%2F202.197.224.171%2Fportal.do';    // 不要去掉?后面的，有bug
        $yzm_url = 'http://202.197.224.171/zfca/captcha.htm';

        $snoopy = new Snoopy;

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
        $logindata = array('username' => $request->sid, 'password' => str_replace(' ', '+', $request->password), 'lt' => $lt, '_eventId' => 'submit', 'j_captcha_response' => $yzm); // 空格换成加号是因为base64编码在url传输中可能把加号变成空格
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
                $passportUser->save();
            } else {
                $passportUser = new PassportUser;
                $passportUser->last_ip = $request->getClientIp();
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
            // 存储绑定数据
            if ($SysPortal = SysPortal::where('sid', '=', $request->sid)->first()) {
                $SysPortal->password = Crypt::encrypt($request->password);  // 加密
                $SysPortal->save();
            } else {
                $SysPortal = new SysPortal;
                $SysPortal->sid = $request->sid;
                $SysPortal->password = Crypt::encrypt($request->password);  // 加密
                $SysPortal->save();
            }

            // 更新一卡通、图书馆、教务三个系统的session
            self::updateSysSession($request, $snoopy);

            // 更新数据库中的session
            return array('code' => 0, 'sid' => $request->sid);
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

    // 教务系统登录，先用数据库中的session尝试，失败在用账密尝试，成功更新库中session
    public static function eduLogin(Request $request)
    {
        $hosturl = 'http://jwxt.xtu.edu.cn/jsxsd/';
        $yzm_url = 'http://jwxt.xtu.edu.cn/jsxsd/verifycode.servlet';
        $posturl = 'http://jwxt.xtu.edu.cn/jsxsd/xk/LoginToXk';
        $mainurl = 'http://jwxt.xtu.edu.cn/jsxsd/framework/xsMain.jsp';
        $flag_url = 'http://jwxt.xtu.edu.cn/jsxsd/xk/LoginToXk?flag=sess';

        $snoopy = new Snoopy;

        // 从数据库中获取session
        if ($sessionid = Sessionid::where('sid', '=', $request->sid)->first()) {
            // 如果能够获取到session
            $snoopy->cookies = json_decode($sessionid->edu, 1);
            $snoopy->fetch($mainurl);
            $data = $snoopy->results;
            if (preg_match('/退出/', $data)) {
                // sessionid 有效
                // 更新ip和时间
                if ($passportUser = PassportUser::where('sid', '=', $request->sid)->first()) {
                    $passportUser->last_ip = $request->getClientIp();
                    $passportUser->save();
                } else {
                    $passportUser = new PassportUser;
                    $passportUser->last_ip = $request->getClientIp();
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
                // sessionid 无效，从数据库获取密码
                if ($sysEdu = SysEdu::where('sid', '=', $request->sid)->first()) {
                    $request->password = Crypt::decrypt($sysEdu->password);
                } else {
                    return array('code' => -2, 'sid' => $request->sid);
                }
                /* 验证码识别 */
                $snoopy->fetch($hosturl);
                $snoopy->setcookies();  //保存cookies
                $snoopy->fetch($yzm_url);
                $yzm_base64 = base64_encode($snoopy->results); //获取图片base64数据
                if (! $yzm = Idcode::EduIdcode($yzm_base64)) {
                    return array('code' => -1);
                }

                /*获取encoded的元值*/
                $flagData = array('USERNAME' => $request->sid, 'PASSWORD' => str_replace(' ', '+', $request->password), 'RANDOMCODE' => $yzm);
                $snoopy->submit($flag_url, $flagData);
                $flag = json_decode($snoopy->results, true)['data'];

                /*计算encoded得值*/
                $code = $request->sid . '%%%' . $request->password;
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
                $logindata = array('USERNAME' => $request->sid, 'PASSWORD' => str_replace(' ', '+', $request->password), 'RANDOMCODE' => $yzm, 'encoded' => $encoded); // 空格换成加号是因为base64编码在url传输中可能把加号变成空格
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
                        $passportUser->save();
                    } else {
                        $passportUser = new PassportUser;
                        $passportUser->last_ip = $request->getClientIp();
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
                        $passportUser->save();
                    } else {
                        $passportUser = new PassportUser;
                        $passportUser->last_ip = $request->getClientIp();
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
                        $passportUser->save();
                    } else {
                        $passportUser = new PassportUser;
                        $passportUser->last_ip = $request->getClientIp();
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
            // 没有获取到session，从数据库获取密码
            if ($sysEdu = SysEdu::where('sid', '=', $request->sid)->first()) {
                $request->password = Crypt::decrypt($sysEdu->password);
            } else {
                return array('code' => -2, 'sid' => $request->sid);
            }
            /* 验证码识别 */
            $snoopy->fetch($hosturl);
            $snoopy->setcookies();  //保存cookies
            $snoopy->fetch($yzm_url);
            $yzm_base64 = base64_encode($snoopy->results); //获取图片base64数据
            if (! $yzm = Idcode::EduIdcode($yzm_base64)) {
                return array('code' => -1);
            }

            /*获取encoded的元值*/
            $flagData = array('USERNAME' => $request->sid, 'PASSWORD' => str_replace(' ', '+', $request->password), 'RANDOMCODE' => $yzm);
            $snoopy->submit($flag_url, $flagData);
            $flag = json_decode($snoopy->results, true)['data'];

            /*计算encoded得值*/
            $code = $request->sid . '%%%' . $request->password;
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
            $logindata = array('USERNAME' => $request->sid, 'PASSWORD' => str_replace(' ', '+', $request->password), 'RANDOMCODE' => $yzm, 'encoded' => $encoded); // 空格换成加号是因为base64编码在url传输中可能把加号变成空格
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
                    $passportUser->save();
                } else {
                    $passportUser = new PassportUser;
                    $passportUser->last_ip = $request->getClientIp();
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
                    $passportUser->save();
                } else {
                    $passportUser = new PassportUser;
                    $passportUser->last_ip = $request->getClientIp();
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
                    $passportUser->save();
                } else {
                    $passportUser = new PassportUser;
                    $passportUser->last_ip = $request->getClientIp();
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

    // 信息门户登录，先用数据库中的session尝试，失败在用账密尝试，成功更新库中session
    public static function portalLogin(Request $request)
    {
        $mainurl = 'http://202.197.224.171/zfca/login';
        $yzm_url = 'http://202.197.224.171/zfca/captcha.htm';

        $snoopy = new Snoopy;

        if ($sessionid = Sessionid::where('sid', '=', $request->sid)->first()) {
            // 如果能够获取到session
            $snoopy->cookies = json_decode($sessionid->portal, 1);
            $snoopy->fetch($mainurl);
            $data = $snoopy->results;
            if (preg_match('/欢迎您:/', $data)) {
                // session 有效，登记访问记录
                // 更新ip和时间
                if ($passportUser = PassportUser::where('sid', '=', $request->sid)->first()) {
                    $passportUser->last_ip = $request->getClientIp();
                    $passportUser->save();
                } else {
                    $passportUser = new PassportUser;
                    $passportUser->last_ip = $request->getClientIp();
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

                // 更新一卡通、图书馆、教务三个系统的session
                self::updateSysSession($request, $snoopy);

                return array('code' => 0, 'cookies' => $snoopy->cookies, 'sid' => $request->sid);
            } else {
                // session 无效，从数据库获取密码
                if ($sysPortal = SysPortal::where('sid', '=', $request->sid)->first()) {
                    $request->password = Crypt::decrypt($sysPortal->password);
                } else {
                    return array('code' => -2, 'sid' => $request->sid);
                }
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
                $logindata = array('username' => $request->sid, 'password' => str_replace(' ', '+', $request->password), 'lt' => $lt, '_eventId' => 'submit', 'j_captcha_response' => $yzm); // 空格换成加号是因为base64编码在url传输中可能把加号变成空格
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
                        $passportUser->save();
                    } else {
                        $passportUser = new PassportUser;
                        $passportUser->last_ip = $request->getClientIp();
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

                    // 更新一卡通、图书馆、教务三个系统的session
                    self::updateSysSession($request, $snoopy);

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
            // 没有获取到session，从数据库获取密码
            if ($sysPortal = SysPortal::where('sid', '=', $request->sid)->first()) {
                $request->password = Crypt::decrypt($sysPortal->password);
            } else {
                return array('code' => -2, 'sid' => $request->sid);
            }
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
            $logindata = array('username' => $request->sid, 'password' => str_replace(' ', '+', $request->password), 'lt' => $lt, '_eventId' => 'submit', 'j_captcha_response' => $yzm); // 空格换成加号是因为base64编码在url传输中可能把加号变成空格
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
                    $passportUser->save();
                } else {
                    $passportUser = new PassportUser;
                    $passportUser->last_ip = $request->getClientIp();
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

                // 更新一卡通、图书馆、教务三个系统的session
                self::updateSysSession($request, $snoopy);
                
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

    // 登录信息门户成功后检查其他各个系统的session，过期则更新session
    public static function updateSysSession(Request $request, $snoopy)
    {
        
        /* 检查并更新一卡通session */
        $ecardLoginUrl = "http://202.197.224.171/zfca/login?yhlx=student&login=0122579031373493732&url=%23";
        $snoopy->maxredirs = 0;
        $snoopy->fetch($ecardLoginUrl);
        $ecardMainUrl = $snoopy->_redirectaddr;
        $ecard = new Snoopy;
        $ecard->maxredirs = 0;
        $ecard->fetch($ecardMainUrl);
        $ecard->setcookies();
        $data = iconv('gbk', 'utf-8', $ecard->results);
        if (preg_match('/持卡人查询界面/', $data)) {
            if ($sessionid = Sessionid::where('sid', '=', $request->sid)->first()) {
                $sessionid->ecard = json_encode($ecard->cookies);
                $sessionid->save();
            } else {
                $sessionid = new Sessionid;
                $sessionid->sid = $request->sid;
                $sessionid->ecard = json_encode($ecard->cookies);
                $sessionid->save();
            }
        }

        /* 检查并更新教务session */
        $eduLoginUrl = "http://202.197.224.171/zfca/login?yhlx=student&login=0122579031373493708&url=null";
        $snoopy->maxredirs = 0;
        $snoopy->fetch($eduLoginUrl);
        $eduMainUrl = $snoopy->_redirectaddr;
        $edu = new Snoopy;
        $edu->fetch($eduMainUrl);
        $edu->setcookies();
        if (preg_match('/学生个人中心/', $edu->results)) {
            if ($sessionid = Sessionid::where('sid', '=', $request->sid)->first()) {
                $sessionid->edu = json_encode($edu->cookies);
                $sessionid->save();
            } else {
                $sessionid = new Sessionid;
                $sessionid->sid = $request->sid;
                $sessionid->edu = json_encode($edu->cookies);
                $sessionid->save();
            }
        }
        /* 检查并更新图书馆session */
        $libLoginUrl = "http://202.197.224.171/zfca/login?yhlx=student&login=0122579031373493694&url=null";
        $snoopy->maxredirs = 0;
        $snoopy->fetch($libLoginUrl);
        $libMainUrl = $snoopy->_redirectaddr;
        $lib = new Snoopy;
        $lib->maxredirs = 0;
        $lib->fetch($libMainUrl);
        $lib->setcookies();
        $lib->fetch("http://202.197.232.4:8081/opac_two/reader/infoList.jsp");
        $data = iconv('gbk', 'utf-8', $lib->results);
        if (preg_match('/ok/', $data)) {
            if ($sessionid = Sessionid::where('sid', '=', $request->sid)->first()) {
                $sessionid->library = json_encode($lib->cookies);
                $sessionid->save();
            } else {
                $sessionid = new Sessionid;
                $sessionid->sid = $request->sid;
                $sessionid->library = json_encode($lib->cookies);
                $sessionid->save();
            }
        }
    }
}
