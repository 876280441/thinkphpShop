<?php


namespace app\index\controller;


use think\Controller;

class Goods extends Base
{
    public function index()
    {
        return $this->fetch('goods');
    }
}
