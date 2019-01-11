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

class PortalTagModel extends Model
{

    /**
     * 关联 文章表
     * @return \think\model\relation\BelongsToMany
     */
    public function articles()
    {
        return $this->belongsToMany('PortalPostModel','portal_tag_post','post_id','tag_id')->alias('post');
    }
}
