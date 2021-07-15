<?php


namespace app\admin\controller;

use think\Controller;
use think\Request;
use app\admin\model\Type as TypeModel;

class Type extends Controller
{
    //商品类型列表
    public function lst()
    {
        $data = TypeModel::order('id desc')->paginate(8);
        $this->assign([
            'title' => '商品类型列表',
            'type' =>$data
        ]);
        return $this->fetch('list');
    }

    //商品类型添加
    public function add()
    {
        if (request()->isPost()){
            $data = input('post.');
            //验证
//            $validate = validate('type');
//            if (!$validate->check($data)){
//                $this->error($validate->getError());
//            }

            //使用DB方式插入数据
            $add = db('type')->insert($data);
            if ($add) {
                $this->success('添加商品类型成功', 'lst');
            } else {
                $this->error('添加商品类型失败');
            }
        }
        $this->assign([
            'title'=>'商品类型添加'
        ]);
        return $this->fetch();
    }
    //删除商品类型
    public function del($id){
        if ($id!=null){
            $result = TypeModel::where('id','=',$id)->delete();
            if ($result) {
                $this->success('删除商品类型成功', 'lst');
            } else {
                $this->error('删除商品类型失败');
            }
        }else{
            $this->error('删除商品类型不能为空');
        }

    }
    //商品类型编辑
    public function edit($id){
        //判断是否是post请求
        if (request()->isPost()) {
            $data = input('post.');
            //验证
//            $validate = validate('type');
//            if (!$validate->check($data)){
//                $this->error($validate->getError());
//            }

            //使用DB方式插入数据
            $save = db('type')->update($data);
            if ($save!==false) {
                $this->success('修改商品类型成功', 'lst');
            } else {
                $this->error('修改商品类型失败');
            }
            return;
        }
        //查询值
        $data = TypeModel::find($id);
        $this->assign([
           'title'=>'商品类型编辑',
            'data'=>$data
        ]);
        return $this->fetch();
    }


}