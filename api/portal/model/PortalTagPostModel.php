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

class PortalTagPostModel extends Model
{
    /**
     * 获取指定id相关的文章id数组
     * @param $post_id  文章id
     * @return array    相关的文章id
     */
    function getRelationPostIds($post_id)
    {
        $tagIds  = $this->where('post_id', $post_id)
            ->column('tag_id');
        $postIds = $this->whereIn('tag_id', $tagIds)
            ->column('post_id');
        return array_unique($postIds);
    }
}