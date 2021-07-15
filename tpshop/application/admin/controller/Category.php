<?php


namespace app\admin\controller;

use app\admin\model\Article as ArticleModel;
use app\admin\model\Brand as BrandModel;
use think\Controller;
use app\admin\model\Category as CategoryModel;
use catetree\Catetree;

class Category extends Controller
{
    //分类列表
    public function lst(){
        $cate = new Catetree();
        $cateObj = new CategoryModel();//实例化表对象
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
            'categoryRes'=>$cateRes
        ]);
        return $this->fetch('list');
    }
    //上传图片
    public function upload(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('cate_img');
        // 移动到框架应用根目录/uploads/ 目录下
        $info = $file->move( '../public/static/uploads');
        if($info){
            // 成功上传后 获取上传信息
            return $info->getSaveName();
        }else{
            // 上传失败获取错误信息
            return $file->getError();
        }
    }
    //分类添加
    public function add(){
        $cate = new Catetree();
        $categoryObj = new CategoryModel();//实例化表对象
        //判断是否为post方式
        if (request()->isPost()){
            $data = input('post.');//获取用户输入的数据

            //处理图片上传
            if ($_FILES['cate_img']['tmp_name']){
                $data['cate_img'] = $this->upload();//把图片地址返回到数值中
            }

            //验证
//            $validate = validate('Cate');
//            if (!!$validate->check($data)){
//                $this->error($validate->getError());
//            }

            $status = $categoryObj->insert($data);
            if ($status){
                $this->success('添加分类成功!',"cate/lst");
            }else{
                $this->error('添加分类失败!');
            }
        }
        $cateRes = $categoryObj->order('sort asc')->select();
        $cateRes = $cate->catetree($cateRes);
        $this->assign([
            'title'=>'商品分类添加',
            'cateRes'=>$cateRes,
        ]);
        return $this->fetch();
    }
    //分类编辑
    public function edit($id){
        $cate = new Catetree();
        $cateObj = new CategoryModel();//实例化表对象
        //判断是否为post方式
        if (request()->isPost()){
            $data = input('post.');//获取用户输入的数据
            //处理图片上传
            if ($_FILES['cate_img']['tmp_name']){
                $data['cate_img'] = $this->upload();//把图片地址返回到数值中
                //查询图片地址
                $categorys = CategoryModel::field('cate_img')->find($data['id']);
                //拼接图片完整地址,IMG_UPLOADS是入口文件定义的自定义系统变量
                $categorysImg = IMG_UPLOADS.$categorys['cate_img'];
                //判断文件是否存在
                if(file_exists($categorysImg)){
                    @unlink($categorysImg);//删除图片,加@是有的无logo
                }
            }
            //验证
//            $validate = validate('Cate');
//            if (!!$validate->check($data)){
//                $this->error($validate->getError());
//            }
            $status = $cateObj->update($data);//更新数据
            if ($status){
                $this->success('修改分类成功!',"cate/lst");
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
        $category = db('cate');
        $cateTree = new Catetree();
        //查询子栏目数据
        $sonids = $cateTree->childrenids($id,$category);
        $sonids[]=intval($id);
        $article =new \app\admin\model\Article();
        //删除分类前判断该分类下的文章和文章相关的缩略图
//        foreach ($sonids as $k=>$v){
//            $artRes = $article->field('id,thumb')->where(array('cate_id'=>$v))->select();
//            foreach ($arrRes as $k1=>$v1) {
//                $thumbSrc = IMG_UPLOADS.$v1['thumb'];
//                //判断图片是否存在
//                if (file_exists($thumbSrc)){
//                    @unlink($thumbSrc);//销毁图片
//                }
//                //删除文章记录
//                $article->delete($v1['id']);
//            }
//        }
        $del = $category->delete($sonids);
        if ($del){
            $this->success('删除分类成功','lst');
        }else{
            $this->error('删除分类失败');
        }
    }

}
