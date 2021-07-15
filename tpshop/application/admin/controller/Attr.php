<?php


namespace app\admin\controller;

use think\Controller;
use think\Request;
use app\admin\model\Attr as AttrModel;

class Attr extends Controller
{
    //商品属性列表
    public function lst()
    {
        $typeId = input('type_id');
        if ($typeId){
            $map['type_id'] = ['=',$typeId];
        }else{
            $map = 1;
        }
        $data = AttrModel::alias('a')->field('a.*,t.type_name')->join('type t',"a.type_id = t.id")->where($map)->order('a.id desc')->paginate(8);
        $this->assign([
            'title' => '商品属性列表',
            'attr' =>$data
        ]);
        return $this->fetch('list');
    }

    //商品属性添加
    public function add()
    {
        if (request()->isPost()){
            $data = input('post.');
            //中文，号替换为英文,号
            $data['attr_values'] = str_replace('，',',',$data['attr_values']);
            //验证
//            $validate = validate('attr');
//            if (!$validate->check($data)){
//                $this->error($validate->getError());
//            }

            //使用DB方式插入数据
            $add = db('attr')->insert($data);
            if ($add) {
                $this->success('添加商品属性成功', 'lst');
            } else {
                $this->error('添加商品属性失败');
            }
        }
        //获取商品所属类型
        $typeRes = \app\admin\model\Type::select();
        $this->assign([
            'title'=>'商品属性添加',
            'TypeRes'=>$typeRes,
        ]);
        return $this->fetch();
    }
    //删除商品属性
    public function del($id){
        if ($id!=null){
            $result = AttrModel::where('id','=',$id)->delete();
            if ($result) {
                $this->success('删除商品属性成功', 'lst');
            } else {
                $this->error('删除商品属性失败');
            }
        }else{
            $this->error('删除商品属性不能为空');
        }

    }
    //商品属性编辑
    public function edit($id){
        //判断是否是post请求
        if (request()->isPost()) {
            $data = input('post.');
            //中文，号替换为英文,号
            $data['attr_values'] = str_replace('，',',',$data['attr_values']);
            //验证
//            $validate = validate('attr');
//            if (!$validate->check($data)){
//                $this->error($validate->getError());
//            }

            //使用DB方式插入数据
            $save = db('attr')->update($data);
            if ($save!==false) {
                $this->success('修改商品属性成功', 'lst');
            } else {
                $this->error('修改商品属性失败');
            }
            return;
        }
        //获取商品所属类型
        $typeRes = \app\admin\model\Type::select();
        //查询值
        $data = AttrModel::find($id);
        $this->assign([
           'title'=>'商品属性编辑',
            'data'=>$data,
            'typeRes'=>$typeRes
        ]);
        return $this->fetch();
    }
    //ajax获取指定类型下的属性
    public function ajaxGetAttr(){
        $typeId = input('type_id');//获取异步发送过来的类型id
        $attrRes = AttrModel::where('type_id','=',$typeId)->select();
        echo json_encode($attrRes);
    }


}