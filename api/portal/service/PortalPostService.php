<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: pl125 <xskjs888@163.com>
// +----------------------------------------------------------------------
namespace api\portal\service;

use api\portal\model\PortalPostModel;
use api\portal\model\PortalCategoryModel;

class PortalPostService extends PortalPostModel
{
    protected $name = "portal_post";

    /**
     * 推荐列表
     * @param int $next_id
     * @param int $num
     * @return array|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function recommendedList($next_id = 0, $num = 10)
    {
        $limit = "{$next_id},{$num}";
        $field = 'id,recommended,user_id,post_like,post_hits,comment_count,create_time,update_time,published_time,post_title,post_excerpt,more';
        $list  = self::with('user')->field($field)->where('recommended', 1)->order('published_time DESC')->limit($limit)->select();
        return $list;
    }

    /**
     * 分类文章列表
     * @param $category_id
     * @param int $next_id
     * @param int $num
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function categoryPostList($category_id, $next_id = 0, $num = 10)
    {
        $limit    = "{$next_id},{$num}";
        $postList = PortalCategoryModel::categoryPostIds($category_id);
        $field    = 'id,recommended,user_id,post_like,post_hits,comment_count,create_time,update_time,published_time,post_title,post_excerpt,more';
        $list     = self::with('user')->field($field)->whereIn('id', $postList['PostIds'])->order('published_time DESC')->limit($limit)->select()->toJson();
        return $list;
    }
}
