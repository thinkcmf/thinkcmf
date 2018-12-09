<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: pl125 <xskjs888@163.com>
// +----------------------------------------------------------------------
namespace api\portal\service;

use api\portal\model\PortalPostModel as PortalPost;
use api\portal\model\PortalCategoryModel as PortalCategory;

class PortalPostModel extends PortalPost
{
    protected $name = "portal_post";

    /**
     * [recommendedList 推荐列表]
     * @Author:   wuwu<15093565100@163.com>
     * @DateTime: 2017-07-17T11:06:47+0800
     * @since:    1.0
     * @param     integer $next_id [最后索引值]
     * @param     integer $num [一页多少条 默认10]
     * @return    [type]                            [数据]
     */
    public static function recommendedList($next_id = 0, $num = 10)
    {
        $limit = "{$next_id},{$num}";
        $field = 'id,recommended,user_id,post_like,post_hits,comment_count,create_time,update_time,published_time,post_title,post_excerpt,more';
        $list  = self::with('user')->field($field)->where('recommended', 1)->order('published_time DESC')->limit($limit)->select();
        return $list;
    }

    /**
     * [categoryPostList 分类文章列表]
     * @Author:   wuwu<15093565100@163.com>
     * @DateTime: 2017-07-17T15:16:26+0800
     * @since:    1.0
     * @param     [type]                   $category_id [分类ID]
     * @param     integer $next_id [limit索引]
     * @param     integer $num [limit每页数量]
     * @return    [type]                                [description]
     */
    public static function categoryPostList($category_id, $next_id = 0, $num = 10)
    {
        $limit    = "{$next_id},{$num}";
        $Postlist = PortalCategory::categoryPostIds($category_id);
        $field    = 'id,recommended,user_id,post_like,post_hits,comment_count,create_time,update_time,published_time,post_title,post_excerpt,more';
        $list     = self::with('user')->field($field)->whereIn('id', $Postlist['PostIds'])->order('published_time DESC')->limit($limit)->select()->toJson();
        return $list;
    }
}
