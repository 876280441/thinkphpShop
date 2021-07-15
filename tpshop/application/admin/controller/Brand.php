<?php


namespace app\admin\controller;

use think\Controller;
use think\Request;
use app\admin\model\Brand as BrandModel;

class Brand extends Controller
{
    //品牌列表
    public function lst()
    {
        $data = BrandModel::order('id desc')->paginate(8);
        $this->assign([
            'title' => '品牌列表',
            'brand' =>$data
        ]);
        return $this->fetch('list');
    }

    //品牌添加
    public function add()
    {
        if (request()->isPost()){
            $data = input('post.');
            //判断用户是否输入了协议前缀
            if ($data['brand_url'] && stripos($data['brand_url'],'http://')===false){
                $data['brand_url'] = 'http://'.$data['brand_url'];
            }
            //处理图片上传
            if ($_FILES['brand_img']['tmp_name']){
                $data['brand_img'] = $this->upload();//把图片地址返回到数值中
            }
            //验证
            $validate = validate('Brand');
            if (!$validate->check($data)){
                $this->error($validate->getError());
            }

            //使用DB方式插入数据
            $add = db('brand')->insert($data);
            if ($add) {
                $this->success('添加品牌成功', 'lst');
            } else {
                $this->error('添加品牌失败');
            }
        }
        $this->assign([
            'title'=>'品牌添加'
        ]);
        return $this->fetch();
    }
    //上传图片
    public function upload(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('brand_img');
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
    //删除品牌
    public function del($id){
        if ($id!=null){
            $result = BrandModel::where('id','=',$id)->delete();
            if ($result) {
                $this->success('删除品牌成功', 'lst');
            } else {
                $this->error('删除品牌失败');
            }
        }else{
            $this->error('删除品牌不能为空');
        }

    }
    //品牌编辑
    public function edit($id){
        //判断是否是post请求
        if (request()->isPost()) {
            $data = input('post.');
            //判断用户是否输入了协议前缀
            if ($data['brand_url'] && stripos($data['brand_url'], 'http://') === false) {
                $data['brand_url'] = 'http://' . $data['brand_url'];
            }
            //处理图片上传
            if ($_FILES['brand_img']['tmp_name']) {
                //查询图片地址
                $old_Brands = BrandModel::field('brand_img')->find($data['id']);
                //拼接图片完整地址,IMG_UPLOADS是入口文件定义的自定义系统变量
                $oldBrandImg = IMG_UPLOADS.$old_Brands['brand_img'];
                //判断文件是否存在
                if(file_exists($oldBrandImg)){
                    @unlink($oldBrandImg);//删除图片,加@是有的无logo
                }
                $data['brand_img'] = $this->upload();//把图片地址返回到数值中
            }
            //验证
            $validate = validate('Brand');
            if (!$validate->check($data)){
                $this->error($validate->getError());
            }

            //使用DB方式插入数据
            $save = db('brand')->update($data);
            if ($save!==false) {
                $this->success('修改品牌成功', 'lst');
            } else {
                $this->error('修改品牌失败');
            }
            return;
        }
        //查询值
        $data = BrandModel::find($id);
        $this->assign([
           'title'=>'品牌编辑',
            'data'=>$data
        ]);
        return $this->fetch();
    }


}