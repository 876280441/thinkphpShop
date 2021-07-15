<?php

namespace app\admin\validate;

use think\Validate;

class Cate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */
	protected $rule = [
	    'cate_name' =>'require|unique:category|min:6'
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */
    protected $message = [
        'cate_name.require'=>'分类名称必须填写',
        'cate_name.unique'=>'分类名称不得重复',
        'cate_name.min'=>'分类名称少于两位',
    ];
}
