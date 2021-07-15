<?php

namespace app\admin\model;

use think\Model;

class Article extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    public function Cate()
    {
        return $this->hasMany('Cate','id');
    }
}
