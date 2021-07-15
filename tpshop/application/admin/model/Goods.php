<?php

namespace app\admin\model;
use think\Model;
use think\Template;
use think\Image;
use app\admin\model\GoodsAttr;
use app\admin\model\GoodsPhoto;

class Goods extends Model
{
    //类型获取器
    public function getOnSaleAttr($value)
    {
        $status = [1=>'上架',0=>'下架'];
        return $status[$value];
    }
    //关联栏目表
    public function Category()
    {
        return $this->belongsTo('Goods');
    }
    //关联品牌表
    public function brand()
    {
        return $this->belongsTo('brand');
    }
    //关联类型表
    public function type()
    {
        return $this->belongsTo('type');//反向一对一
    }
    //关联库存表
    public function Product(){
        //正向一对一
        return $this->hasOne('product','goods_id','id');
    }
    protected $field = true;//忽略数据库中没有存在的字段
    protected static function init()
    {
        //执行添加成功之前操作
        Goods::beforeInsert(function ($goods){
            //生成商品主图的三张缩略图
            if ($_FILES['og_thumb']['tmp_name']){
                $thumbName = $goods->upload('og_thumb');//保存图片名
                $Imgurl = '../public/static/uploads/';
                //原图地址
                $ogThumb =$Imgurl.date("Ymd")."/".$thumbName;
                //大图地址
                $bigThumb = $Imgurl.date("Ymd")."/"."big_".$thumbName;
                //中图地址
                $mdThumb = $Imgurl.date("Ymd")."/"."md_".$thumbName;
                //小图地址
                $smThumb = $Imgurl.date("Ymd")."/"."sm_".$thumbName;
                $image = Image::open($ogThumb);//打开原图
                // 按照原图的比例生成一个缩略图并保存
                $image->thumb(500, 500)->save($bigThumb);//大缩略图
                $image->thumb(200, 200)->save($mdThumb);//中缩略图
                $image->thumb(80, 80)->save($smThumb);//小缩略图
                $bigThumb = str_replace("..","/tpshop",$bigThumb);
                $ogThumb = str_replace("..","/tpshop",$ogThumb);
                $mdThumb = str_replace("..","/tpshop",$mdThumb);
                $smThumb = str_replace("..","/tpshop",$smThumb);
                $goods->og_thumb = $ogThumb;//插入原图
                $goods->big_thumb = $bigThumb;//插入大缩略图
                $goods->md_thumb = $mdThumb;//插入中缩略图
                $goods->sm_thumb = $smThumb;//插入小缩略图
            }
            $goods->goods_code = time().rand(111111,999999);//商品编号
        });
        //执行添加成功之后操作
        Goods::afterInsert(function ($goods){
           //批量写入会员价格
            $mppriceArr = $goods->mpprice;//获取输入的会员价格
            $goodsId = $goods->id;//获取商品id
            //判断是否有会员价格
            if ($mppriceArr){
                foreach ($mppriceArr as $k=>$v){
                    if (trim($v)==''){
                        continue;
                    }else{
                        db('member_price')->insert(['mlevel_id'=>$k,'mpprice'=>$v,'goods_id'=>$goodsId]);
                    }
                }
            }
            //处理商品属性
            $goodsData = input("post.");
            $i = 0;//价格数组索引
            if (isset($goodsData['goods_attr'])){
                //处理属性
                foreach ($goodsData['goods_attr'] as $k=>$v){
                    if (is_array($v)){
                        //处理单选属性
                        if (!empty($v)){
                            //处理价格
                            foreach ($v as $k1=>$v1){
                                if ($v1==='请选择'){
                                    $i++;
                                    continue ;
                                }
                                db('goods_attr')->insert(['attr_id'=>$k,'attr_value'=>$v1,'attr_price'=>$goodsData['attr_price'][$i],'goods_id'=>$goodsId]);
                                $i++;//由于属性与属性值都是一一对应的，顺序也是对应的
                            }
                        }
                    }else{
                        //处理唯一属性类型
                        db('goods_attr')->insert(['attr_id'=>$k,'attr_value'=>$v,'goods_id'=>$goodsId]);
                    }
                }
            }
            //商品相册处理
            if ($goods->_hasImgs($_FILES['goods_photo']['tmp_name'])){
                //$photoName = $goods->uploads('goods_photo');//保存图片名
                // 获取表单上传文件
                $files = request()->file('goods_photo');
                foreach($files as $file){
                    // 移动到框架应用根目录/uploads/ 目录下
                    $info = $file->move( '../public/static/uploads');
                    if($info){
                        // 成功上传
                        $Imgurl = '../public/static/uploads/';
                        //获取图片名字
                        $photoName = $info->getFilename();
                        //原图地址
                        $ogphoto =$Imgurl.date("Ymd")."/".$photoName;
                        $Imgurls = "/tpshop/public/static/uploads/";
                        //大图地址
                        $bigphoto = $Imgurl.date("Ymd")."/"."big_".$photoName;
                        //中图地址
                        $mdphoto = $Imgurl.date("Ymd")."/"."md_".$photoName;
                        //小图地址
                        $smphoto = $Imgurl.date("Ymd")."/"."sm_".$photoName;
                        $image = Image::open($ogphoto);//打开原图
                        // 按照原图的比例生成一个缩略图并保存
                        $image->thumb(500, 500)->save($bigphoto);//大缩略图
                        $image->thumb(200, 200)->save($mdphoto);//中缩略图
                        $image->thumb(80, 80)->save($smphoto);//小缩略图
                        $bigphoto = str_replace("..","/tpshop",$bigphoto);
                        $mdphoto = str_replace("..","/tpshop",$mdphoto);
                        $smphoto = str_replace("..","/tpshop",$smphoto);
                        //插入缩略图
                        db('goods_photo')->insert(['goods_id'=>$goodsId,'sm_photo'=>$smphoto,'md_photo'=>$mdphoto,'bg_photo'=>$bigphoto,'og_photo'=>$ogphoto]);
                    }else{
                        // 上传失败获取错误信息
                        return $file->getError();
                    }
                }
               }
        });
        //执行更新操作之前执行
        Goods::beforeUpdate(function ($goods){
            $goodsId = $goods->id;//获取商品id
            $goodsData = input("post.");
            //添加商品属性
            if (isset($goodsData['goods_attr'])){
                $i = 0;//价格数组索引
                //处理属性
                foreach ($goodsData['goods_attr'] as $k=>$v){
                    if (is_array($v)){
                        //处理单选属性
                        if (!empty($v)){
                            //处理价格
                            foreach ($v as $k1=>$v1){
                                if ($v1==='请选择'){
                                    $i++;
                                    continue ;
                                }
                                db('goods_attr')->insert(['attr_id'=>$k,'attr_value'=>$v1,'attr_price'=>$goodsData['attr_price'][$i],'goods_id'=>$goodsId]);
                                $i++;//由于属性与属性值都是一一对应的，顺序也是对应的
                            }
                        }
                    }else{
                        //处理唯一属性类型
                        db('goods_attr')->insert(['attr_id'=>$k,'attr_value'=>$v,'goods_id'=>$goodsId]);
                    }
                }
            }
            //编辑商品属性
            if (isset($goodsData['old_goods_attr'])){
                $attrPrice = $goodsData['old_attr_price'];
                $keyAttr = array_keys($attrPrice);//获取键
                $valuesAttr = array_values($attrPrice);//获取值
//                dump($keyAttr);
//                dump($valuesAttr);die();
                $i = 0;//价格数组索引
                //处理属性
                foreach ($goodsData['old_goods_attr'] as $k=>$v){
                    if (is_array($v)){
                        //处理单选属性
                        if (!empty($v)){
                            //处理价格
                            foreach ($v as $k1=>$v1){
                                if ($v1==='请选择'){
                                    $i++;
                                    continue ;
                                }
                                db('goods_attr')->where('id','=',$keyAttr[$i])->update(['attr_value'=>$v1,'attr_price'=>$valuesAttr[$i]]);
                                $i++;//由于属性与属性值都是一一对应的，顺序也是对应的
                            }
                        }
                    }else{
                        //处理唯一属性类型
                        db('goods_attr')->where('id','=',$keyAttr[$i])->update(['attr_value'=>$v,'attr_price'=>$valuesAttr[$i]]);
                        $i++;
                    }
                }
            }
            //更新商品相册处理
            if ($goods->_hasImgs($_FILES['goods_photo']['tmp_name'])){
                //$photoName = $goods->uploads('goods_photo');//保存图片名
                // 获取表单上传文件
                $files = request()->file('goods_photo');
                foreach($files as $file){
                    // 移动到框架应用根目录/uploads/ 目录下
                    $info = $file->move( '../public/static/uploads');
                    if($info){
                        // 成功上传
                        $Imgurl = '../public/static/uploads/';
                        //获取图片名字
                        $photoName = $info->getFilename();
                        //原图地址
                        $ogphoto =$Imgurl.date("Ymd")."/".$photoName;
                        $Imgurls = "/tpshop/public/static/uploads/";
                        //大图地址
                        $bigphoto = $Imgurl.date("Ymd")."/"."big_".$photoName;
                        //中图地址
                        $mdphoto = $Imgurl.date("Ymd")."/"."md_".$photoName;
                        //小图地址
                        $smphoto = $Imgurl.date("Ymd")."/"."sm_".$photoName;
                        $image = Image::open($ogphoto);//打开原图
                        // 按照原图的比例生成一个缩略图并保存
                        $image->thumb(500, 500)->save($bigphoto);//大缩略图
                        $image->thumb(200, 200)->save($mdphoto);//中缩略图
                        $image->thumb(80, 80)->save($smphoto);//小缩略图
                        $bigphoto = str_replace("..","/tpshop",$bigphoto);
                        $mdphoto = str_replace("..","/tpshop",$mdphoto);
                        $smphoto = str_replace("..","/tpshop",$smphoto);
                        //插入缩略图
                        db('goods_photo')->insert(['goods_id'=>$goodsId,'sm_photo'=>$smphoto,'md_photo'=>$mdphoto,'bg_photo'=>$bigphoto,'og_photo'=>$ogphoto]);
                    }else{
                        // 上传失败获取错误信息
                        return $file->getError();
                    }
                }
            }
            //更新会员价格
            //批量写入会员价格
            $mppriceArr = $goods->mpprice;//获取输入的会员价格
            //删除原有会员价格
            MemberPrice::where('goods_id','=',$goodsId)->delete();
            //判断是否有会员价格
            if ($mppriceArr){
                //批量写入会员价格
                foreach ($mppriceArr as $k=>$v){
                    if (trim($v)==''){
                        continue;
                    }else{
                        db('member_price')->insert(['mlevel_id'=>$k,'mpprice'=>$v,'goods_id'=>$goodsId]);
                    }
                }
            }
            //修改商品之前，如果有上传新的缩略图，先处理图片
            //生成商品主图的三张缩略图
            if ($_FILES['og_thumb']['tmp_name']){
                //如果存在缩略图，就先删除旧的缩略图，再生成新的缩略图
                $goods->og_thumb = str_replace("/tpshop","..",$goods->og_thumb);
                $goods->sm_thumb = str_replace("/tpshop","..",$goods->sm_thumb);
                $goods->big_thumb = str_replace("/tpshop","..",$goods->big_thumb);
                $goods->md_thumb = str_replace("/tpshop","..",$goods->md_thumb);
                @unlink($goods->og_thumb);
                @unlink($goods->sm_thumb);
                @unlink($goods->big_thumb);
                @unlink($goods->md_thumb);
                //上传新的缩略图
                $thumbName = $goods->upload('og_thumb');//保存图片名
                $Imgurl = '../public/static/uploads/';
                //原图地址
                $ogThumb =$Imgurl.date("Ymd")."/".$thumbName;
                //大图地址
                $bigThumb = $Imgurl.date("Ymd")."/"."big_".$thumbName;
                //中图地址
                $mdThumb = $Imgurl.date("Ymd")."/"."md_".$thumbName;
                //小图地址
                $smThumb = $Imgurl.date("Ymd")."/"."sm_".$thumbName;
                $image = Image::open($ogThumb);//打开原图
                // 按照原图的比例生成一个缩略图并保存
                $image->thumb(500, 500)->save($bigThumb);//大缩略图
                $image->thumb(200, 200)->save($mdThumb);//中缩略图
                $image->thumb(80, 80)->save($smThumb);//小缩略图
                $bigThumb = str_replace("..","/tpshop",$bigThumb);
                $ogThumb = str_replace("..","/tpshop",$ogThumb);
                $mdThumb = str_replace("..","/tpshop",$mdThumb);
                $smThumb = str_replace("..","/tpshop",$smThumb);
                $goods->og_thumb = $ogThumb;//插入原图
                $goods->big_thumb = $bigThumb;//插入大缩略图
                $goods->md_thumb = $mdThumb;//插入中缩略图
                $goods->sm_thumb = $smThumb;//插入小缩略图
            }
        });
        //执行删除商品之前操作
        Goods::beforeDelete(function ($goods){
            $goodsId = $goods->id;//获取商品id
//            //删除主图及其缩略图
            if ($goods->og_thumb){
                $thumb[]  = [];
                $thumb[] = $goods->og_thumb;
                $thumb[] = $goods->big_thumb;
                $thumb[] = $goods->md_thumb;
                $thumb[] = $goods->sm_thumb;
                //循环缩略图地址
                foreach ($thumb as $k=>$v){
                    $v = str_replace("/tpshop","..",$v);
                    @unlink($v);//删除商品图片
                }
            }
            //删除关联的会员价格
            db('member_price')->where('goods_id','=',$goodsId)->delete();
            //删除关联的商品属性
            db('goods_attr')->where('goods_id','=',$goodsId)->delete();
            //删除关联的商品相册
            $goodsPhotoRes  = GoodsPhoto::where('goods_id','=',$goodsId)->select();
                foreach ($goodsPhotoRes  as $k=>$v){
                    if ($v->og_photo){
                        $photo[]  = [];
                        $photo[] = $v->og_photo;
                        $photo[] = $v->bg_photo;
                        $photo[] = $v->md_photo;
                        $photo[] = $v->sm_photo;
                        foreach ($photo as $k1=>$v1){
                            $v1 = str_replace("/tpshop","..",$v1);
                            @unlink($v1);//删除商品图片
                        }
                }
            }
                //删除记录，在数据库的记录
                $goodsPhotoRes  = GoodsPhoto::where('goods_id','=',$goodsId)->delete();
        });
    }
    //判断是否有商品图片上传
    protected function _hasImgs($tmpArr){
        foreach ($tmpArr as $k=>$v){
            if ($v){
                return true;
            }
        }
        return false;
    }
    //上传图片(单图片处理)
    public function upload($imgName){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file($imgName);
        // 移动到框架应用根目录/uploads/ 目录下
        $info = $file->move( '../public/static/uploads');
        if($info){
            // 成功上传后 获取上传信息
            return $info->getFilename();
        }else{
            // 上传失败获取错误信息
            return $file->getError();
        }
    }
    //上传图片(多图片处理)
    public function uploads($imgNames){
        // 获取表单上传文件
        $files = request()->file($imgNames);
        foreach($files as $file){
            // 移动到框架应用根目录/uploads/ 目录下
            $info = $file->move( '../public/static/uploads');
            if($info){
                // 成功上传后 获取上传信息
                return $info->getFilename();
            }else{
                // 上传失败获取错误信息
                return $file->getError();
            }
        }
    }
}
