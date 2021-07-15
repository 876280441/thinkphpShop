<?php


namespace app\index\model;


use think\Model;

//文章模型
class Article extends Model
{
    public function getFooterArts()
    {
        //获取底部帮助分类
        $helpCateRes = model('cate')->where('cate_type', '=', 3)->order('sort asc')->select();
        foreach ($helpCateRes as $k => $v) {
            $helpCateRes[$k]['arts'] = $this->where('cate_id', $v['id'])->select();
        }

        return $helpCateRes;
    }
}
