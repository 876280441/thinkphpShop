<?php

namespace app\admin\model;

use think\Model;

class Link extends Model
{
    //类型获取器
    public function getTypeAttr($value)
    {
        $status = [1=>'文字链接',2=>'图片链接'];
        return $status[$value];
    }
}
