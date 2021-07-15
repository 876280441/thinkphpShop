<?php


namespace app\admin\controller;

use app\admin\model\Category as CategoryModel;
use app\admin\model\GoodsAttr;
use app\admin\model\GoodsPhoto;
use catetree\Catetree;
use think\Controller;
use think\Request;
use app\admin\model\Goods as GoodsModel;
use app\admin\model\MemberLevel;
use app\admin\model\Type;
use app\admin\model\Brand;
use app\admin\model\Product;
use app\admin\model\MemberPrice;
use app\admin\model\Attr;

class Goods extends Controller
{
    //商品列表
    public function lst()
    {
        //withSum统计
        $data = GoodsModel::withSum('Product','goods_number')->order('id desc')->paginate(8);
        $this->assign([
            'title' => '商品列表',
            'goods' =>$data,
        ]);
        return $this->fetch('list');
    }

    //商品添加
    public function add()
    {
        if (request()->isPost()){
            $data = input('post.');
//            dump($data);exit();
            //价格类型转换
            $data['markte_price'] = (float)$data['markte_price'];
            $data['shop_price'] = (float)$data['shop_price'];
            //验证
            $validate = validate('goods');
            if (!$validate->check($data)){
                $this->error($validate->getError());
            }

            //使用模型的办法添加数据
            $add = model('goods')->save($data);
            if ($add) {
                $this->success('添加商品成功', 'lst');
            } else {
                $this->error('添加商品失败');
            }
        }
        //会员级别数据
        $mlRes = MemberLevel::field('id,level_name')->select();
        //商品类型
        $type = Type::select();
        //商品分类
        $cate = new Catetree();
        $categoryObj = new CategoryModel();//实例化表对象
        $cateRes = $categoryObj->order('sort asc')->select();
        $cateRes = $cate->catetree($cateRes);
        //品牌分类
        $brandRes = Brand::field('id,brand_name')->select();
        $this->assign([
            'title'=>'商品添加',
            'mlRes'=>$mlRes,
            'type'=>$type,
            'brandRes'=>$brandRes,
            'cateRes'=>$cateRes
        ]);
        return $this->fetch();
    }
    //删除商品
    public function del($id){
        if ($id!=null){
            $GoodsModel = new GoodsModel();
            $result = $GoodsModel->destroy($id);
            if ($result) {
                $this->success('删除商品成功', 'lst');
            } else {
                $this->error('删除商品失败');
            }
        }else{
            $this->error('删除商品不能为空');
        }

    }
    //商品编辑
    public function edit($id){
        if (request()->isPost()){
            $data = input('post.');
//            dump($data);exit();
            //价格类型转换
            $data['markte_price'] = (float)$data['markte_price'];
            $data['shop_price'] = (float)$data['shop_price'];
            //验证
            $validate = validate('goods');
            if (!$validate->check($data)){
                $this->error($validate->getError());
            }

            //使用模型的办法编辑数据
            $add = model('goods')->update($data);
            if ($add) {
                $this->success('编辑商品成功', 'lst');
            } else {
                $this->error('编辑商品失败');
            }
        }
        //会员级别数据
        $mlRes = MemberLevel::field('id,level_name')->select();
        //商品类型
        $type = Type::select();
        //商品分类
        $cate = new Catetree();
        $categoryObj = new CategoryModel();//实例化表对象
        //获取无限极分类信息
        $cateRes = $categoryObj->order('sort asc')->select();
        $cateRes = $cate->catetree($cateRes);
        //品牌分类
        $brandRes = Brand::field('id,brand_name')->select();
        //商品基本数据
        $goodsjb = GoodsModel::where('id','=',$id)->select();
        //会员价格
        $_price = MemberPrice::where('goods_id','=',$id)->select();
        $price = array();
        //重组会员价格数组，把会员等级id做为键，然后在前端做判断的时候就比较灵活
        foreach ($_price as $k=>$v){
            $price[$v['mlevel_id']] = $v;
        }
        //商品相册
        $gphoto = GoodsPhoto::where('goods_id','=',$id)->select();
        //查询当前商品类型所有属性信息
        $attrRes = Attr::where('type_id','=',$goodsjb[0]['type_id'])->select();
        //查询当前商品拥有的商品属性goods_attr
        $_gattrRes = GoodsAttr::where('goods_id','=',$id)->select();
        $gattrRes = array();
        foreach ($_gattrRes as $k=>$v){
            $gattrRes[$v['attr_id']][] = $v;
        }
//        return json($gattrRes);exit();
        $this->assign([
            'title'=>'商品编辑',
            'mlRes'=>$mlRes,
            'type'=>$type,
            'brandRes'=>$brandRes,
            'cateRes'=>$cateRes,
            'goodsjb'=>$goodsjb,
            'price'=>$price,
            'gphoto'=>$gphoto,
            'attrRes'=>$attrRes,
            'gattrRes'=>$gattrRes
        ]);
        return $this->fetch();
    }
    //商品库存
    public function product($id){
        $product = db('product');
        //处理提交的数据
        if (request()->isPost()){
            $data = input('post.');
            //在接收修改数据的时候，把当前id中的库存信息全部删除，再重新添加进去，实现信息的更新
            Product::where('goods_id','=',$id)->delete();//删除商品库存信息
            $goodsAttr = $data['goods_attr'];//商品属性
            $goodsNum = $data['goods_num'];//商品库存
            foreach ($goodsNum as $k => $v) {
                $strArr = array();
                foreach ($goodsAttr as $k1 => $v1) {
                    if (intval($v1[$k])<=0){
                        continue 2;
                    }
                    $strArr[] = $v1[$k];
                }
                sort($strArr);
                $strArr = implode(',', $strArr);
                $product->insert([
                    'goods_id' => $id,
                    'goods_number' => $v,
                    'goods_attr' => $strArr
                ]);
            }
            $this->success('添加库存成功', 'goods/lst');
            }
        //获取单选属性
        $_radioAttrRes = db('goods_attr')->alias('g')
            ->field('g.id,g.attr_id,g.attr_value,a.attr_name')
            ->join('attr a','g.attr_id=a.id')->where(array('g.goods_id'=>$id,'a.attr_type'=>1))
            ->order('a.attr_values','desc')->select();
        $radioAttrRes = array();
        foreach ($_radioAttrRes as $k=>$v){
            $radioAttrRes[$v['attr_name']][] = $v;
        }
//        dump($radioAttrRes);exit();
        //获取商品的库存信息
        $goodsProRes = Product::where('goods_id','=',$id)->order('goods_attr','desc')->select();
        $this->assign([
           'title'=>'库存',
            'radioAttrRes'=>$radioAttrRes,
            'goodsProRes'=>$goodsProRes
        ]);
        return $this->fetch();
    }
    //异步删除商品相册图片
    public function ajaxdelpic($id){
        $gphoto = GoodsPhoto::find($id);
        $bigphoto = str_replace("/tpshop","..",$gphoto['bg_photo']);
        $mdphoto = str_replace("/tpshop","..",$gphoto['md_photo']);
        $smphoto = str_replace("/tpshop","..",$gphoto['sm_photo']);
        $ogphoto = str_replace("/tpshop","..",$gphoto['og_photo']);
        @unlink($bigphoto);
        @unlink($mdphoto);
        @unlink($smphoto);
        @unlink($ogphoto);
        $data = GoodsPhoto::where('id','=',$id)->delete();
        if ($data){
            echo 1;
        }else{
            echo 2;
        }
    }

}
