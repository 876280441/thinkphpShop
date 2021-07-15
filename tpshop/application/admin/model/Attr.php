<?php

namespace app\admin\model;

use think\Model;

class Attr extends Model
{
    public function Type()
    {
        return $this->hasMany('Type','id');
    }

    //类型获取器
    public function getAttrTypeAttr($value)
    {
        $status = [1=>'单选',2=>'唯一'];
        return $status[$value];
    }
}
