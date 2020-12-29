<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------
namespace cmf\model;

use think\Db;
use think\Model;

class UserModel extends Model
{
    /**
     * 模型名称
     * @var string
     */
    protected $name = 'user';

    /**
     * 是否需要自动写入时间戳 如果设置为字符串 则表示时间字段的类型
     * @var bool|string
     */
    protected $autoWriteTimestamp = true;

    /**
     * 更新时间字段 false表示关闭
     * @var false|string
     */
    protected $updateTime = false;

    /**
     * 数据表字段类型
     * @var array
     */
    protected $type = [
        'more' => 'array',
    ];


}
