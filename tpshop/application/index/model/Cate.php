<?php


namespace app\index\model;


use think\Model;

class Cate extends Model
{
    //普通分类
    public function getComCates()
    {
        //获取分类数据,普通分类的顶级分类
        $comCates = $this->where(array('cate_type' => 5, 'pid' => 0))->select();
        //有二级分类则查询
        foreach ($comCates as $k => $v) {
            $comCates[$k]['children'] = $this->where(array('pid' => $v['id']))->select();
        }
        return $comCates;
    }

    //网店帮助分类
    public function shoHelpCates()
    {
        $helpCates = $this->where(array('cate_type' => 3, 'pid' => 3))->select();
        return $helpCates;
    }
}
