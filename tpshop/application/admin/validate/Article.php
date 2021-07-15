<?php

namespace app\admin\validate;

use think\Validate;

class Article extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'title'=>'require|unique:article',
        'cate_id'=>'require',
        'email'=>'email',
        'link_url'=>'url',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'title.require'=>'标题必须填写',
        'title.unique'=> '标题不能重复',
        'cate_id.require'=>'所属栏目不能为空',
        'link_url.url'=>'URL地址格式错误'
    ];
}
