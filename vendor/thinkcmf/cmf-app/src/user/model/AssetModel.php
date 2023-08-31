<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\user\model;

use think\Model;

class AssetModel extends Model
{
    /**
     * 模型名称
     * @var string
     */
    protected $name = 'asset';

    /**
     * 关联 user表
     * @return $this
     */
    public function user()
    {
        return $this->belongsTo('UserModel', 'user_id');
    }
}
