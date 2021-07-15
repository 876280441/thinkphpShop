<?php


namespace app\admin\controller;
use app\admin\model\Link as LinkModel;
use think\Controller;

class Link extends Controller
{
    //链接列表
    public function lst()
    {
        $data = LinkModel::order('id desc')->paginate(8);
        $this->assign([
            'title' => '链接列表',
            'Link' =>$data
        ]);
        return $this->fetch('list');
    }

    //链接添加
    public function add()
    {
        if (request()->isPost()){
            $data = input('post.');
            //判断用户是否输入了协议前缀
            if ($data['link_url'] && stripos($data['link_url'],'http://')===false){
                $data['link_url'] = 'http://'.$data['link_url'];
            }
            //处理图片上传
            if ($_FILES['logo']['tmp_name']){
                $data['logo'] = $this->upload();//把图片地址返回到数值中
            }
            //验证
//            $validate = validate('Brand');
//            if (!$validate->check($data)){
//                $this->error($validate->getError());
//            }

            //使用DB方式插入数据
            $add = db('link')->insert($data);
            if ($add) {
                $this->success('添加链接成功', 'lst');
            } else {
                $this->error('添加链接失败');
            }
        }
        $this->assign([
            'title'=>'链接添加'
        ]);
        return $this->fetch();
    }
    //上传图片
    public function upload(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('logo');
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
    //删除链接
    public function del($id){
        $logo = LinkModel::field('logo')->find($id);
        if ($logo){
            $linkImg = IMG_UPLOADS.$logo['logo'];
            //判断图片是否存在
            if (file_exists($linkImg)){
                @unlink($linkImg);//销毁图片
            }
        }
        if ($id!=null){
            $result = LinkModel::where('id','=',$id)->delete();
            if ($result) {
                $this->success('删除链接成功', 'lst');
            } else {
                $this->error('删除链接失败');
            }
        }else{
            $this->error('删除链接不能为空');
        }

    }
    //链接编辑
    public function edit($id){
        //判断是否是post请求
        if (request()->isPost()) {
            $data = input('post.');
            //判断用户是否输入了协议前缀
            if ($data['link_url'] && stripos($data['link_url'], 'http://') === false) {
                $data['link_url'] = 'http://' . $data['link_url'];
            }
            //处理图片上传
            if ($_FILES['logo']['tmp_name']) {
                //查询图片地址
                $old_Brands = LinkModel::field('logo')->find($data['id']);
                //拼接图片完整地址,IMG_UPLOADS是入口文件定义的自定义系统变量
                $oldBrandImg = IMG_UPLOADS.$old_Brands['logo'];
                //判断文件是否存在
                if(file_exists($oldBrandImg)){
                    @unlink($oldBrandImg);//删除图片,加@是有的无logo
                }
                $data['logo'] = $this->upload();//把图片地址返回到数值中
            }
            //验证
            $validate = validate('Link');
            if (!$validate->check($data)){
                $this->error($validate->getError());
            }

            //使用DB方式插入数据
            $save = db('link')->update($data);
            if ($save!==false) {
                $this->success('修改链接成功', 'lst');
            } else {
                $this->error('修改链接失败');
            }
            return;
        }
        //查询值
        $data = LinkModel::find($id);
        $this->assign([
           'title'=>'链接编辑',
            'data'=>$data
        ]);
        return $this->fetch();
    }


}