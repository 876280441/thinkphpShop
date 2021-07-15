<?php

namespace app\admin\validate;

use think\Validate;

class Brand extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'brand_name' => 'require|unique:brand',
	    'brand_url' => 'require|url',
        'brand_description'=>'min:6'
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'brand_name.require'=>'品牌名称必须填写',
        'brand_name.unique'=>'品牌名称不能重复',
        'brand_url.require'=>'品牌官网必须填写',
        'brand_url.url'=>'url格式错误',
        'brand_description.min'=>'描述最少6个字符',
    ];
}
