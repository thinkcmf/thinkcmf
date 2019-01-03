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
use think\db\Query;

class PortalPostService
{
    //模型关联方法
    protected $relationFilter = ['user'];
    /**
     * 文章列表
     * @param      $filter
     * @param bool $isPage
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function postArticles($filter, $isPage = false)
    {
        $join = [];

        $field    = empty($filter['field']) ? 'a.*' : $filter['field'];
        $page     = empty($filter['page']) ? '' : $filter['page'];
        $limit    = empty($filter['limit']) ? '' : $filter['limit'];
        $order    = empty($filter['order']) ? ['-update_time'] : explode(',', $filter['order']);
        $category = empty($filter['category']) ? 0 : intval($filter['category']);
        if (!empty($category)) {
            array_push($join, [
                '__PORTAL_CATEGORY_POST__ b', 'a.id = b.post_id'
            ]);
            $field = 'a.*,b.id AS post_category_id,b.list_order,b.category_id';
        }

        $orderArr = [];
        foreach ($order as $key => $value) {
            $upDwn      = substr($value, 0, 1);
            $orderType  = $upDwn == '-' ? 'desc' : 'asc';
            $orderField = substr($value, 1);
            if (!empty($whiteParams)) {
                if (in_array($orderField, $whiteParams)) {
                    $orderArr[$orderField] = $orderType;
                }
            } else {
                $orderArr[$orderField] = $orderType;
            }
        }

        $portalPostModel = new PortalPostModel();


        if (!empty($page)) {
            $portalPostModel = $portalPostModel->page($page);
        } elseif (!empty($limit)) {
            $portalPostModel = $portalPostModel->limit($limit);
        } else {
            $portalPostModel = $portalPostModel->limit(10);
        }

        $articles = $portalPostModel
            ->alias('a')
            ->field($field)
            ->join($join)
            ->where('a.create_time', '>=', 0)
            ->where('a.delete_time', 0)
            ->where(function (Query $query) use ($filter, $isPage) {
                if (!empty($filter['user_id'])) {
                    $query->where('a.user_id', $filter['user_id']);
                }
                $category = empty($filter['category']) ? 0 : intval($filter['category']);
                if (!empty($category)) {
                    $query->where('b.category_id', $category);
                }
                $startTime = empty($filter['start_time']) ? 0 : strtotime($filter['start_time']);
                $endTime   = empty($filter['end_time']) ? 0 : strtotime($filter['end_time']);
                if (!empty($startTime)) {
                    $query->where('a.published_time', '>=', $startTime);
                }
                if (!empty($endTime)) {
                    $query->where('a.published_time', '<=', $endTime);
                }
                $keyword = empty($filter['keyword']) ? '' : $filter['keyword'];
                if (!empty($keyword)) {
                    $query->where('a.post_title', 'like', "%$keyword%");
                }
                if ($isPage) {
                    $query->where('a.post_type', 2);
                } else {
                    $query->where('a.post_type', 1);
                }
            })
            ->order($orderArr)
            ->select();

        return $articles;
    }

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

        $portalPostModel = new PortalPostModel();
        $list            = $portalPostModel->with('user')->field($field)->where('recommended', 1)->order('published_time DESC')->limit($limit)->select();
        return $list;
    }

    /**
     * 分类文章列表
     * @param     $category_id
     * @param int $next_id
     * @param int $num
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function categoryPostList($category_id, $next_id = 0, $num = 10)
    {
        $portalPostModel = new PortalPostModel();
        $limit           = "{$next_id},{$num}";
        $postList        = PortalCategoryModel::categoryPostIds($category_id);
        $field           = 'id,recommended,user_id,post_like,post_hits,comment_count,create_time,update_time,published_time,post_title,post_excerpt,more';
        $list            = $portalPostModel
            ->with('user')
            ->field($field)->whereIn('id', $postList['PostIds'])
            ->order('published_time DESC')
            ->limit($limit)->select()
            ->toJson();
        return $list;
    }
    /**
     * 模型检查
     * @param $relations
     * @return array|bool
     */
    public function allowedRelations($relations)
    {
        if (is_string($relations)) {
            $relations = explode(',', $relations);
        }
        if (!is_array($relations)) {
            return false;
        }
        return array_intersect($this->relationFilter, $relations);
    }
}
