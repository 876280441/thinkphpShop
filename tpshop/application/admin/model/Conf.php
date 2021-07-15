<?php

namespace app\admin\model;

use think\Model;

class Conf extends Model
{
    //类型获取器
    public function getConfTypeAttr($value)
    {
        $status = [1=>'网店配置',2=>'商品配置'];
        return $status[$value];
    }
}
