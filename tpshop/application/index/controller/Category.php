<?php


namespace app\index\controller;


use think\Controller;

class Category extends Base
{
    public function index()
    {
        return $this->fetch('category');
    }
}
