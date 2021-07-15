<?php


namespace app\index\controller;


use think\Controller;

class Cate extends Base
{
    public function index()
    {
        //从模型获取数据,普通分类
        $comCates = model('cate')->getComCates();
        //从模型获取数据,帮助分类
        $helpCates = model('cate')->shoHelpCates();
        $this->assign([
            'comCates' => $comCates,
            'helpCates' => $helpCates
        ]);
        return $this->fetch('cate');
    }
}
