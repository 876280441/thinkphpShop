<?php


namespace app\index\controller;


use think\Controller;

class Base extends Controller
{
    public function initialize()
    {
        $this->_getFooterArts();
    }

    private function _getFooterArts()
    {
        $helpCateRes = model('Article')->getFooterArts();
        $this->assign([
            'helpCateRes' => $helpCateRes
        ]);
    }
}
