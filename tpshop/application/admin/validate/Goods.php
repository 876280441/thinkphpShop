<?php

namespace app\admin\validate;

use think\Validate;

class Goods extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */
	protected $rule = [
	    'goods_name'=>'require',
        'category_id'=>'require',
        'markte_price'=>'require|float',
        'shop_price'=>'require|float',
        'goods_weight'=>'require|float',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */
    protected $message = [
        'goods_name.require'=>'商品名称不得为空',
        'goods_name.unique'=>'商品名称已存在',
        'category_id.require'=>'所属栏目必须填写',
        'markte_price.require'=>'商品市场价必须填写',
        'markte_price.float'=>'商品市场价必须为数字',
        'shop_price.require'=>'商品促销价必须填写',
        'shop_price.float'=>'商品促销价必须为数字',
        'goods_weight.require'=>'商品重量必须填写',
        'goods_weight.float'=>'商品重量必须为数字',
    ];
}
