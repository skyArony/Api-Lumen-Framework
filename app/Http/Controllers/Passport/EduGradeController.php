<?php

namespace App\Http\Controllers\Passport;

use App\Libs\Snoopy;
use Illuminate\Http\Request;
use App\Models\PassportCore;
use App\Http\Controllers\ApiController;

/* 
 *  系统：统一登录系统
 *  功能：查成绩
 * 
 */
class EduGradeController extends ApiController
{
    // 获取成绩
    public function getGrade(Request $request)
    {
        $gradeData_url = "http://jwxt.xtu.edu.cn/jsxsd/kscj/cjcx_list";
        $termCode_url = "http://jwxt.xtu.edu.cn/jsxsd/kscj/cjcx_query";
        $snoopy = new Snoopy;

        if ($request->has("sid", "edupd")) {
            /* 学期代码数组 */
            $startTerm = (int)(substr($request->sid, 0, 4));
            $allTerms = array('占位');
            for ($i = 0; $i < 4; $i++) {
                $str1 = ($startTerm + $i) . "-" . ($startTerm + $i + 1) . "-1";
                $str2 = ($startTerm + $i) . "-" . ($startTerm + $i + 1) . "-2";
                $allTerms[] = $str1;
                $allTerms[] = $str2;
            }
            
            /* 查询所有成绩 */
            $array = PassportCore::eduLogin($request);
            if ($array['code'] >= 0 && $array['sid'] == $request->sid) {
                $snoopy->cookies = $array['cookies'];
                $snoopy->fetch($gradeData_url);
                if ($snoopy->status != 200) {
                    // 成绩获取失败
                    return $this->createResponse(null, 500, -1);
                }
                /* 数据预处理 */
                $data =$snoopy->results;
                $data = str_replace(' color:red;', ' ', $data);
                $isCom = '';    // 是否评教
            } else {
                if ($array['code'] == -1 || $array['code'] == -2) {
                    return $this->createResponse(null, 500, $array['code']);
                } elseif ($array['code'] == -3 || $array['code'] == -4) {
                    return $this->createResponse(null, 400, $array['code']);
                }
            }

            /* 根据查询条件筛选数据 */
            $term = $this->route_parameter('term');
            if ($term == 'all') {
                // 查询所有数据
                if (preg_match("/<td>请评教<\/td>/", $data)) {
                    $isCom = "未评教";
                    $preg = '/<td>(.*?)<\/td>\s+<td align="left">(.*?)<\/td>\s+<t(.*?)>(.*?)<\/td>\s+<td>(.*?)<\/td>\s+<td>(.*?)<\/td>\s+<td>(.*?)<\/td>\s+<td>(.*?)<\/td>\s+<td>(.*?)<\/td>\s+<\/tr>/';
                } else {
                    $preg = '/<td>(.*?)<\/td>\s+<td align="left">(.*?)<\/td>\s+<td style=" "><a href="(.*?)">(.*?)<\/a><\/td>\s+<td>(.*?)<\/td>\s+<td>(.*?)<\/td>\s+<td>(.*?)<\/td>\s+<td>(.*?)<\/td>\s+<td>(.*?)<\/td>\s+<\/tr>/';
                }
                preg_match_all($preg, $data, $matches);
            } elseif ($term > 0 && $term < 9) {
                /* 返回选择的学期成绩 */
                if (preg_match("/<td>请评教<\/td>/", $data)) {
                    $isCom = "未评教";
                    $preg = '/<td>('. $allTerms[$term] .')<\/td>\s+<td align="left">(.*?)<\/td>\s+<t(.*?)>(.*?)<\/td>\s+<td>(.*?)<\/td>\s+<td>(.*?)<\/td>\s+<td>(.*?)<\/td>\s+<td>(.*?)<\/td>\s+<td>(.*?)<\/td>\s+<\/tr>/';
                } else {
                    $preg = '/<td>('. $allTerms[$term] .')<\/td>\s+<td align="left">(.*?)<\/td>\s+<td style=" "><a href="(.*?)">(.*?)<\/a><\/td>\s+<td>(.*?)<\/td>\s+<td>(.*?)<\/td>\s+<td>(.*?)<\/td>\s+<td>(.*?)<\/td>\s+<td>(.*?)<\/td>\s+<\/tr>/';
                }
                preg_match_all($preg, $data, $matches);
            } elseif ($term == 0) {
                $termCodes = array_reverse($allTerms);
                foreach ($termCodes as $key => $value) {
                    if (preg_match('/<td>'. $value .'<\/td>\s+<td align="left">/', $data)) {
                        $termCode = $value;
                        break;
                    }
                }
                if (preg_match("/<td>请评教<\/td>/", $data)) {
                    $isCom = "未评教";
                    $preg = '/<td>('. $termCode .')<\/td>\s+<td align="left">(.*?)<\/td>\s+<t(.*?)>(.*?)<\/td>\s+<td>(.*?)<\/td>\s+<td>(.*?)<\/td>\s+<td>(.*?)<\/td>\s+<td>(.*?)<\/td>\s+<td>(.*?)<\/td>\s+<\/tr>/';
                } else {
                    $preg = '/<td>('. $termCode .')<\/td>\s+<td align="left">(.*?)<\/td>\s+<td style=" "><a href="(.*?)">(.*?)<\/a><\/td>\s+<td>(.*?)<\/td>\s+<td>(.*?)<\/td>\s+<td>(.*?)<\/td>\s+<td>(.*?)<\/td>\s+<td>(.*?)<\/td>\s+<\/tr>/';
                }
                preg_match_all($preg, $data, $matches);
            } else {
                return $this->createResponse(null, 400, -65535);
            }

            /* 数据整合 */
            $num = count($matches[1]);
            if ($num == 0) {
                return $this->createResponse(null, 404, -11);
            }
            for ($i = 1; $i <= $num; $i++) {
                $term = array_keys($allTerms, $matches[1][$i-1], true); //获取学期代码
                $studentData[$i-1]['course']                  = $matches[2][$i-1];
                $studentData[$i-1]['type']                    = $matches[8][$i-1];
                $studentData[$i-1]['class_type']              = $matches[9][$i-1];
                $studentData[$i-1]['credit']                  = $matches[5][$i-1];
                $studentData[$i-1]['daily_grade']             = 'null';
                $studentData[$i-1]['exam_grade']              = 'null';
                if ($isCom) {
                    $studentData[$i-1]['comp_grade']          = "未评教";
                } else {
                    $studentData[$i-1]['comp_grade']          = $matches[4][$i-1];
                }
                $studentData[$i-1]['term']                    = $term[0];
                $studentData[$i-1]['test_way']                = $matches[7][$i-1];
                $studentData[$i-1]['study_time']              = $matches[6][$i-1];
                $isPass = 1;
                if ($matches[4][$i-1] < 60 || $matches[4][$i-1] == "不及格") {
                    $isPass = 0;
                }
                if ($matches[4][$i-1] == "优" || $matches[4][$i-1] == "良" || $matches[4][$i-1] == "中等" || $matches[4][$i-1] == "及格" || $matches[4][$i-1] == "优秀" || $matches[4][$i-1] == "良好" || $matches[4][$i-1] == "中") {
                    $isPass = 1;
                }
                $studentData[$i-1]['isPass']                  = $isPass;
            }
            return $this->createResponse(json_encode($studentData, JSON_UNESCAPED_UNICODE), 200, 0, $snoopy->cookies['JSESSIONID']);
        } else {
            return $this->createResponse(null, 400, -65535);
        }
    }

    // 解析路由路径中的参数
    protected function route_parameter($name, $default = null)
    {
        $routeInfo = app('request')->route();

        return array_get($routeInfo[2], $name, $default);
    }
}
