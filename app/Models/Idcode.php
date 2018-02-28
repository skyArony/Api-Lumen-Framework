<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/*
 * 系统：统一登录系统
 * 功能： 验证码识别
 *
*/
class Idcode extends Model
{
    // 教务系统验证码识别
    public static function EduIdcode($imgBase64)
    {
        // 写入图片到临时文件夹
        $filename = '../storage/app/idcodeTemp/'.md5(microtime()).".jpg";
        $fre = file_put_contents($filename, base64_decode($imgBase64));
        // 图片写入失败
        if (! $fre > 0) {
            return false;
        }
        // 从数据库获取字符数据
        $strData = DB::connection('mysql-passport')->select('select * from idcode_edu');
        $strData = json_decode(json_encode($strData), true);
        for ($a = 0; count($strData) > $a; $a++) {
            $strData[$a]['bits'] = unserialize($strData[$a]['bits']);
        }
        // 获取图片属性
        list($width, $height, $type, $attr) = getimagesize($filename);
        $imgDeal = imagecreatetruecolor($width, $height); //处理缓冲区
        $imgSource = imagecreatefromjpeg($filename); //获取图片
        // 图片获取失败
        if (!$imgSource) {
            return false;
        }
        $imgContainer = imagecreatetruecolor($width, $height); //缓冲区
        imagecopy($imgContainer, $imgSource, 0, 0, 0, 0, $width, $height); //绘出图片
        $valueBox=array();
        $valueColorArea1=array_fill(0, 256, 0);
        $valueColorAreaAver1=0;
        for ($x=0; $x < $width; $x++) {
            $valueBox[]=array();
            for ($y=0; $y < $height; $y++) {
                $color_index = imagecolorat($imgSource, $x, $y);
                $color_tran = imagecolorsforindex($imgSource, $color_index);
                $color_aver = intval(($color_tran['red'] + $color_tran['green'] + $color_tran['blue']) / 3);
                if ($color_aver < 200 && $color_tran['blue'] < 200) {
                    $color_aver = 0;
                } else {
                    $color_aver = 255;
                }
                $valueBox[$x][] = $color_aver;
            }
        }
        $clipArea=array(3,13,23,33,43);
        $idcode="";
        $idcodeId="";
        $clipAreaValueUnit=array();
        $confirms=array();
        for ($a=0;$a<count($clipArea)-1;$a++) {
            $rect_x= array($clipArea[$a],$clipArea[$a+1]);
            $top=$height;
            $left=$rect_x[1];
            $right=$rect_x[0];
            $bottom=0;
            for ($b=$rect_x[0];$b<$rect_x[1];$b++) {
                for ($c=0;$c<$height;$c++) {
                    if ($valueBox[$b][$c]==0) {
                        if ($c<$top) {
                            $top=$c;
                        }
                        if ($b<$left) {
                            $left=$b;
                        }
                        if ($c>$bottom) {
                            $bottom=$c;
                        }
                        if ($b>$right) {
                            $right=$b;
                        }
                    }
                }
            }
            $rect_size_source=array($right-$left+1,$bottom-$top+1);
            $clipAreaValueUnit[]=array();
            $rect_size= array(20,20);
            for ($b=0;$b<$rect_size[0];$b++) {
                $clipAreaValueUnit[$a][]= array();
                for ($c=3;$c<$rect_size[1]-5;$c++) {
                    $_x=$left+intval($b*$rect_size_source[0]/$rect_size[0]);
                    $_y=$top+intval($c*$rect_size_source[1]/$rect_size[1]);
                    $bit=0;
                    if ($valueBox[$_x][$_y]==0) {
                        $bit=1;
                    }
                    $clipAreaValueUnit[$a][$b][]=$bit;
                }
            }
            $confirms[]=array();
            for ($b=0;$b<count($strData);$b++) {
                $match=0;
                $allSelf=0;
                $allStan=0;
                $differ=0;
                for ($c=0;$c<$rect_size[0];$c++) {
                    for ($d=0;$d<$rect_size[1]-8;$d++) {
                        if ($clipAreaValueUnit[$a][$c][$d]==$strData[$b]['bits'][$c][$d]) {
                            $match++;
                        } elseif ($strData[$b]['bits'][$c][$d]>$clipAreaValueUnit[$a][$c][$d]) {
                            $match--;
                        } else {
                            $match-=0.5;
                        }
                        if ($clipAreaValueUnit[$a][$c][$d]>0) {
                            $allSelf++;
                        }
                        if ($strData[$b]['bits'][$c][$d]>0) {
                            $allStan++;
                        }
                    }
                }
                $all=max($allSelf, $allStan);
                $confirms[$a][]=$match/$all;
            }
            $mostMatch=-100;
            //echo "<br>";
            for ($b=0;$b<count($confirms[$a]);$b++) {
                //echo $confirms[$a][$b]." ".$strData[$b]['str']."<br>";
                if ($mostMatch<$confirms[$a][$b]) {
                    $mostMatch=$confirms[$a][$b];
                    $mostLike=$strData[$b]['str'];
                    $idcode_id=$strData[$b]['id'];
                }
            }
            $idcode.=$mostLike;
            $idcodeId.=$idcode_id." ";
        }
        //echo "identify:".(microtime(true)-$t1)." s<br>";
        //$t1 = microtime(true);
        //ob_start ();
        //  imagejpeg($imgSource);
        //  $image_data = ob_get_contents ();
        //ob_end_clean ();

        //$image_data_base64 = base64_encode ($image_data);
        //echo "<img src='data:image/png;base64,".$image_data_base64."'/><br>".$idcode;
        return $idcode;
    }

    // 信息门户验证码识别
    public static function PortalIdcode($imgBase64)
    {
        // 写入图片到临时文件夹
        $filename = '../storage/app/idcodeTemp/'.md5(microtime()).".jpg";
        $fre = file_put_contents($filename, base64_decode($imgBase64));
        // 图片写入失败
        if (! $fre > 0) {
            return false;
        }
        // 从数据库获取字符数据
        $strData = DB::connection('mysql-passport')->select('select * from idcode_portal');
        $strData = json_decode(json_encode($strData), true);
        for ($a = 0; count($strData) > $a; $a++) {
            $strData[$a]['bits'] = unserialize($strData[$a]['bits']);
        }
        // 获取图片属性
        list($width, $height, $type, $attr) = getimagesize($filename);
        $imgDeal = imagecreatetruecolor($width, $height); //处理缓冲区
        $imgSource = imagecreatefromjpeg($filename); //获取图片
        // 图片获取失败
        if (!$imgSource) {
            return false;
        }
        $imgContainer=imagecreatetruecolor($width, $height);//缓冲区
        imagecopy($imgContainer, $imgSource, 0, 0, 0, 0, $width, $height);//绘出图片
        $valueBox=array();
        $valueColorArea1=array_fill(0, 256, 0);
        $valueColorAreaAver1=0;
        for ($x=0;$x<$width;$x++) {
            $valueBox[]=array();
            for ($y=0;$y<$height;$y++) {
                $color_index = imagecolorat($imgSource, $x, $y);
                $color_tran = imagecolorsforindex($imgSource, $color_index);
                $color_aver = intval(($color_tran['red']+$color_tran['green']+$color_tran['blue'])/3);
                if ($color_aver<140) {
                    $color_aver=0;
                } else {
                    $color_aver=255;
                }
                $valueBox[$x][]=$color_aver;
            }
        }
        $clipArea=array(1,16,36,56,74);
        $idcode="";
        $idcodeId="";
        $clipAreaValueUnit=array();
        $confirms=array();
        for ($a=0;$a<count($clipArea)-1;$a++) {
            $rect_x= array($clipArea[$a],$clipArea[$a+1]);
            $top=$height;
            $left=$rect_x[1];
            $right=$rect_x[0];
            $bottom=0;
            for ($b=$rect_x[0];$b<$rect_x[1];$b++) {
                for ($c=0;$c<$height;$c++) {
                    if ($valueBox[$b][$c]==0) {
                        if ($c<$top) {
                            $top=$c;
                        }
                        if ($b<$left) {
                            $left=$b;
                        }
                        if ($c>$bottom) {
                            $bottom=$c;
                        }
                        if ($b>$right) {
                            $right=$b;
                        }
                    }
                }
            }
            $rect_size_source=array($right-$left+1,$bottom-$top+1);
            $clipAreaValueUnit[]=array();
            $rect_size= array(12,20);
            for ($b=0;$b<$rect_size[0];$b++) {
                $clipAreaValueUnit[$a][]= array();
                for ($c=0;$c<$rect_size[1];$c++) {
                    $_x=$left+intval($b*$rect_size_source[0]/$rect_size[0]);
                    $_y=$top+intval($c*$rect_size_source[1]/$rect_size[1]);
                    $bit=0;
                    if ($valueBox[$_x][$_y]==0) {
                        $bit=1;
                    }
                    $clipAreaValueUnit[$a][$b][]=$bit;
                }
            }
            $confirms[]=array();
            for ($b=0;$b<count($strData);$b++) {
                $match=0;
                $allSelf=0;
                $allStan=0;
                $differ=0;
                for ($c=0;$c<$rect_size[0];$c++) {
                    for ($d=0;$d<$rect_size[1]-8;$d++) {
                        if ($clipAreaValueUnit[$a][$c][$d]==$strData[$b]['bits'][$c][$d]) {
                            $match+=1;
                        } elseif ($strData[$b]['bits'][$c][$d]>$clipAreaValueUnit[$a][$c][$d]) {
                            $match-=1;
                        } else {
                            $match-=1;
                        }
                        if ($clipAreaValueUnit[$a][$c][$d]>0) {
                            $allSelf++;
                        }
                        if ($strData[$b]['bits'][$c][$d]>0) {
                            //$allStan+=2;
                        }
                    }
                }
                $all=$allSelf+$allStan;
                $confirms[$a][]=$match/$all;
            }
            $mostMatch=-100;
            //echo "<br>";
            for ($b=0;$b<count($confirms[$a]);$b++) {
                //echo $confirms[$a][$b]." ".$strData[$b]['str']."<br>";
                if ($mostMatch<$confirms[$a][$b]) {
                    $mostMatch=$confirms[$a][$b];
                    $mostLike=$strData[$b]['str'];
                    $idcode_id=$strData[$b]['id'];
                }
            }
            $idcode.=$mostLike;
            $idcodeId.=$idcode_id." ";
        }
        //echo "identify:".(microtime(true)-$t1)." s<br>";
        //$t1 = microtime(true);
        //ob_start ();
        //  imagejpeg($imgSource);
        //  $image_data = ob_get_contents ();
        //ob_end_clean ();

        //$image_data_base64 = base64_encode ($image_data);
        //echo "<img src='data:image/png;base64,".$image_data_base64."'/><br>".$idcode;
        return $idcode;
    }
}
