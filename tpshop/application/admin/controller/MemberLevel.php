<?php


namespace app\admin\controller;

use think\Controller;
use think\Request;
use app\admin\model\MemberLevel as MemberLevelModel;

class MemberLevel extends Controller
{
    //会员等级列表
    public function lst()
    {
        $data = MemberLevelModel::order('id desc')->paginate(8);
        $this->assign([
            'title' => '会员等级列表',
            'Level' =>$data
        ]);
        return $this->fetch('memberlevel/list');
    }

    //会员等级添加
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
            $add = db('MemberLevel')->insert($data);
            if ($add) {
                $this->success('添加会员等级成功', 'lst');
            } else {
                $this->error('添加会员等级失败');
            }
        }
        $this->assign([
            'title'=>'会员等级添加'
        ]);
        return $this->fetch('memberlevel/add');
    }
    //删除会员等级
    public function del($id){
        if ($id!=null){
            $result = MemberLevelModel::where('id','=',$id)->delete();
            if ($result) {
                $this->success('删除会员等级成功', 'lst');
            } else {
                $this->error('删除会员等级失败');
            }
        }else{
            $this->error('删除会员等级不能为空');
        }
    }
    //会员等级编辑
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
            $save = db('member_level')->update($data);
            if ($save!==false) {
                $this->success('修改会员等级成功', 'member_level/lst');
            } else {
                $this->error('修改会员等级失败');
            }
            return;
        }
        //查询值
        $data = MemberLevelModel::find($id);
        $this->assign([
           'title'=>'会员等级编辑',
            'data'=>$data
        ]);
        return $this->fetch('memberlevel/edit');
    }


}