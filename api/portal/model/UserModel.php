<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: pl125 <xskjs888@163.com>
// +----------------------------------------------------------------------

namespace api\portal\model;


use think\Model;

class UserModel extends Model
{

    //模型关联方法
    protected $relationFilter = ['user'];


    /**
     * more 自动转化
     * @param $value
     * @return string
     */
    public function getAvatarAttr($value)
    {
        $value = !empty($value) ? cmf_get_image_url($value) : $value;
        return $value;
    }

    /**
     * 关联 user表
     * @return string 关联数据
     */
    public function user()
    {
        return $this->belongsTo('UserModel', 'user_id')->setEagerlyType(1);
    }
}
