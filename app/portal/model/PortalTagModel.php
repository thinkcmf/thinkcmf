<?php

// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author:kane < chengjin005@163.com>
// +----------------------------------------------------------------------

namespace app\portal\model;

use think\Model;

class PortalTagModel extends Model
{
    public static $STATUS = [
        0=> '未启用',
        1=> '已启用',
    ];
}
