<?php

namespace app\admin\validate;

use think\Validate;

class Attr extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'type_id'=>'require',
        'attr_name'=>'require|unique:attr',
        'attr_values'=>'require',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'type_id.require'=>'所属分类必须填写',
        'attr_name.require'=>'属性名称必须填写',
        'attr_values.require'=>'属性值必须填写',
        'attr_name.unique'=>'属性名称不得重复'
    ];
}
