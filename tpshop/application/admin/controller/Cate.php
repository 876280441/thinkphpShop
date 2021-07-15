<?php


namespace app\admin\controller;

use think\Controller;
use app\admin\model\Cate as CateModel;
use catetree\Catetree;

class Cate extends Controller
{
    //分类列表
    public function lst(){
        $cate = new Catetree();
        $cateObj = new CateModel();//实例化表对象
        if (request()->isPost()){
            //获取用户输入的排序数据
            $data = input('post.');
            $cate->cateSort($data['sort'],$cateObj);
            $this->success('排序成功',url('lst'));
        }
        $cateRes = $cateObj->order('sort asc')->select();
        $cateRes = $cate->catetree($cateRes);
        $this->assign([
           'title'=>'分类管理',
            'cateRes'=>$cateRes
        ]);
        return $this->fetch('list');
    }
    //分类添加
    public function add(){
        $cate = new Catetree();
        $cateObj = new CateModel();//实例化表对象
        //判断是否为post方式
        if (request()->isPost()){
            $data = input('post.');//获取用户输入的数据
            //验证
            $validate = validate('Cate');
            if (!!$validate->check($data)){
                $this->error($validate->getError());
            }
            //判断是否 可以添加子栏目
            if (in_array($data['pid'],['1','3'])){
                $this->error("系统分类不能作为上级栏目");
            }
            if ($data['pid']==2){
                $data['cate_type'] = 3;
            }
            $cateId = CateModel::field('pid')->find($data['pid']);
            $cateId  = $cateId['pid'];
            if ($cateId==2){
                $this->error("此分类不能作为上级分类");
            }
            //判断分类能不能 作为上级分类

            $status = $cateObj->save([
               'cate_name'=>$data['cate_name'],
                'pid'=>$data['pid'],
                'description'=>$data['description'],
                'keywords'=>$data['keywords'],
                'show_nav'=>$data['show_nav'],
            ]);
            if ($status){
                $this->success('添加分类成功!',"category/lst");
            }else{
                $this->error('添加分类失败!');
            }
        }
        $cateRes = $cateObj->order('sort asc')->select();
        $cateRes = $cate->catetree($cateRes);
        //判断是否为post方式
        $this->assign([
            'title'=>'文章添加',
            'cateRes'=>$cateRes,
        ]);
        return $this->fetch();
    }
    //分类编辑
    public function edit($id){
        $cate = new Catetree();
        $cateObj = new CateModel();//实例化表对象
        //判断是否为post方式
        if (request()->isPost()){
            $data = input('post.');//获取用户输入的数据
            //验证
            $validate = validate('Cate');
            if (!!$validate->check($data)){
                $this->error($validate->getError());
            }
            $status = $cateObj->update($data);//更新数据
            if ($status){
                $this->success('修改分类成功!',"category/lst");
            }else{
                $this->error('修改分类失败!');
            }
        }
        $cates = $cateObj->find(input('id'));
        $cateRes = $cateObj->order('sort asc')->select();
        $cateRes = $cate->catetree($cateRes);
        $this->assign([
            'title'=>'修改分类',
            'cateRes'=>$cateRes,
            'cates'=>$cates
        ]);
        return $this->fetch();
    }

    //分类删除
    public function del($id){
        //实例化分类对象
        $cate = db('category');
        $cateTree = new Catetree();
        //查询子栏目数据
        $sonids = $cateTree->childrenids($id,$cate);
        $sonids[]=intval($id);
        $arrSys = [1,2,3];//设定系统文章分类
        $arrRes  = array_intersect($arrSys,$sonids);//取交集，如果有出现交集的情况，就不能删除
        if ($arrRes){
            $this->error('系统内置文章分类不 允许删除');
        }
        $article =new \app\admin\model\Article();
        //删除分类前判断该分类下的文章和文章相关的缩略图
        foreach ($sonids as $k=>$v){
            $artRes = $article->field('id,thumb')->where(array('cate_id'=>$v))->select();
            foreach ($arrRes as $k1=>$v1) {
                $thumbSrc = IMG_UPLOADS.$v1['thumb'];
                //判断图片是否存在
                if (file_exists($thumbSrc)){
                    @unlink($thumbSrc);//销毁图片
                }
                //删除文章记录
                $article->delete($v1['id']);
            }
        }
        $del = $cate->delete($sonids);
        if ($del){
            $this->success('删除分类成功','lst');
        }else{
            $this->error('删除分类失败');
        }
    }

}
