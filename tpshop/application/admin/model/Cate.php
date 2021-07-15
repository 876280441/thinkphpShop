<?php

namespace app\admin\model;

use think\Model;

class Cate extends Model
{
    public function getCateTypeAttr($value)
    {
        $status = [1=>'系统分类',2=>'帮助分类',3=>'网店帮助',4=>'网店信息',5=>'普通分类',0=>'顶级分类'];
        return $status[$value];
    }
}
