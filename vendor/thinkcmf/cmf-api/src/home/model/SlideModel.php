<?php
// +----------------------------------------------------------------------
// | 文件说明：用户-幻灯片
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: wuwu <15093565100@163.com>
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Date: 2017-5-25
// +----------------------------------------------------------------------

namespace api\home\model;

use think\Model;

class SlideModel extends Model
{

    /**
     * 一对一关联模型 关联分类下的幻灯片
     * @return \think\model\relation\HasMany
     */
    protected function items()
    {
        return $this->hasMany('SlideItemModel')->order('list_order ASC');
    }

}

