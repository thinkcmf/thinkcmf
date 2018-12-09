<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: pl125 <xskjs888@163.com>
// +----------------------------------------------------------------------

namespace api\portal\model;
use api\common\model\CommonModel;

class UserModel extends CommonModel
{
    //可查询字段
    protected $visible = [
        'user_nickname', 'avatar', 'signature','user_url','user_login','birthday','sex'
    ];
    //模型关联方法
    protected $relationFilter = ['user'];

    /**
     * 基础查询
     */
    protected function base($query)
    {
        $query->alias('user')->where('user.user_status', 1);
    }

    /**
     * more 自动转化
     * @param $value
     * @return array
     */
    public function getAvatarAttr($value)
    {
        $value = !empty($value) ? cmf_get_image_url($value) : $value;
        return $value;
    }

    /**
     * 关联 user表
     * @return $this
     */
    public function user()
    {
        return $this->belongsTo('UserModel', 'user_id')->setEagerlyType(1);
    }
}
