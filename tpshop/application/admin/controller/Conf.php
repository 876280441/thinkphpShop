<?php


namespace app\admin\controller;

use app\admin\model\Cate as CateModel;
use catetree\Catetree;
use think\Controller;
use think\Request;
use app\admin\model\Conf as ConfModel;

class Conf extends Controller
{

    public function conflist(){
        $conf = new ConfModel();
        if (request()->isPost()){
            $data = input('post.');
            //复选框空选问题
            //查询出所有类型为CheckBox的数据
            $checkFileds2D = $conf->field('ename')->where(array('form_type'=>'checkbox'))->select();
            //将CheckBox类型的数据改为一维数组
            $checkFileds = array();//置为空
            if ($checkFileds2D){
                foreach ($checkFileds2D as $k=>$v){
                    $checkFileds[]=$v['ename'];
                }
            }
            //所有发送的字段组成的数组
            $allFields= array();
            //处理文字数据
            foreach ($data as $k=>$v){
                $allFields[]  = $k;
                if (is_array($v)){
                    $value = implode(',',$v);
                    $conf->where(array('ename'=>$k))->update(['value'=>$value]);
                }else{
                    $conf->where(array('ename'=>$k))->update(['value'=>$v]);
                }
            }
            //如果复选框未选中任何一个选项，就设置为空
            foreach ($checkFileds as $k=>$v){
                if (!in_array($v,$allFields)){
                    $conf->where(array('ename'=>$v))->update(['value'=>'']);
                }
            }
            //处理图片数据
            if ($_FILES){//判断是否选择了上传文件
                //循环遍历上传文件数组，因为有可能有多个上传文件
                foreach ($_FILES as $k=>$v){
                    if ($v['tmp_name']){
                        $imgs = $conf->field('value')->where(array('ename'=>$k))->find();
                        if ($imgs['value']){
                            //拼接图片完整地址,IMG_UPLOADS是入口文件定义的自定义系统变量
                            $oldImg = IMG_UPLOADS.$imgs['value'];
                            //判断文件是否存在
                            if(file_exists($oldImg)){
                                @unlink($oldImg);//删除图片,加@是有的无logo
                            }
                        }
                        $imgSrc = $this->upload($k);//返回上传 的文件夹和文件名字
                        //写入到数据库中
                        $conf->where(array('ename'=>$k))->update(['value'=>$imgSrc]);
                    }
                }
            }
            $this->success('配置成功');
        }
        //店铺相关配置
        $ShopConfRes = $conf->where(array('conf_type'=>1))->order('sort asc')->select();
        //商品相关配置
        $GoodsConfRes = $conf->where(array('conf_type'=>2))->order('sort asc')->select();
        $this->assign([
           'title'=>'配置项',
            'ShopConfRes'=>$ShopConfRes,
            'GoodsConfRes'=>$GoodsConfRes
        ]);
        return $this->fetch();
    }

    //配置列表
    public function lst()
    {
        $confObj = new ConfModel();//实例化表对象
        if (request()->isPost()){
            //获取用户输入的排序数据
            $data = input('post.');
            foreach ($data['sort'] as $k=>$v){
                $confObj->where('id','=',$k)->update(['sort'=>$v]);
            }
            $this->success('排序成功',url('lst'));
        }
        $data = ConfModel::order('sort asc')->paginate(8);
        $this->assign([
            'title' => '配置列表',
            'conf' =>$data
        ]);
        return $this->fetch('list');
    }

    //配置添加
    public function add()
    {
        if (request()->isPost()){
            $data = input('post.');

            //如果是可选的，就把中文的，号替换成英文的,
            if ($data['form_type']=='radio'||$data['form_type']=='checkbox'||$data['form_type']=='select'){
                $data['values'] = str_replace('，',',',$data['values']);
                $data['value'] = str_replace('，',',',$data['value']);
            }

            //验证
            $validate = validate('Conf');
            if (!$validate->check($data)){
                $this->error($validate->getError());
            }

            //使用DB方式插入数据
            $add = db('conf')->insert($data);
            if ($add) {
                $this->success('添加配置成功', 'lst');
            } else {
                $this->error('添加配置失败');
            }
        }
        $this->assign([
            'title'=>'配置添加'
        ]);
        return $this->fetch();
    }
    //上传图片
    public function upload($imgName){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file($imgName);
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
    //删除配置
    public function del($id){
        if ($id!=null){
            $result = ConfModel::where('id','=',$id)->delete();
            if ($result) {
                $this->success('删除配置成功', 'lst');
            } else {
                $this->error('删除配置失败');
            }
        }else{
            $this->error('删除配置不能为空');
        }

    }
    //配置编辑
    public function edit($id){
        //判断是否是post请求
        if (request()->isPost()) {
            $data = input('post.');
            //如果是可选的，就把中文的，号替换成英文的,
            if ($data['form_type']=='radio'||$data['form_type']=='checkbox'||$data['form_type']=='select'){
                $data['values'] = str_replace('，',',',$data['values']);
                $data['value'] = str_replace('，',',',$data['value']);
            }
            //验证
            $validate = validate('Conf');
            if (!$validate->check($data)){
                $this->error($validate->getError());
            }

            //使用DB方式插入数据
            $save = db('conf')->update($data);
            if ($save!==false) {
                $this->success('修改配置成功', 'lst');
            } else {
                $this->error('修改配置失败');
            }
            return;
        }
        //查询值
        $data = ConfModel::find($id);
//        return json($data);
        $this->assign([
           'title'=>'配置编辑',
            'data'=>$data
        ]);
        return $this->fetch();
    }


}