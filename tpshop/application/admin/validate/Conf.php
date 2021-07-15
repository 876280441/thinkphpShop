<?php

namespace app\admin\validate;

use think\Validate;

class Conf extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'ename'=>'require|min:2',
	    'cname'=>'require|min:2',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'ename.require'=>'配置英文名称必须填写',
        'cname.require'=>'配置中文名称必须填写',
        'cname.min'=>'配置中文名称长度必须大于2',
        'ename.min'=>'配置英文名称长度必须大于2',
    ];
}
