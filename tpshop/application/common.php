<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

//$str为要进行截取的字符串，$length为截取长度（汉字算一个字，字母算半个字）
function strCut($str,$length)
{
    $str = trim($str);
    $string = "";
    if(strlen($str) > $length)
    {
        for($i = 0 ; $i<$length ; $i++)
        {
            if(ord($str) > 127)
            {
                $string .= $str[$i] . $str[$i+1] . $str[$i+2];
                $i = $i + 2;
            }
            else
            {
                $string .= $str[$i];
            }
        }
        $string .= "......";
        return $string;
    }
    return $str;
}

