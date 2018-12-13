<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\portal\service;

use app\portal\model\PortalPostModel;

class PostService
{
    /**
     * 文章查询
     * @param $filter
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function adminArticleList($filter)
    {
        return $this->adminPostList($filter);
    }

    /**
     * 页面文章列表
     * @param $filter
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function adminPageList($filter)
    {
        return $this->adminPostList($filter, true);
    }

    /**
     * 文章查询
     * @param $filter
     * @param bool $isPage
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function adminPostList($filter, $isPage = false)
    {

        $where = [
            'a.create_time' => ['>=', 0],
            'a.delete_time' => 0
        ];

        $join = [
            ['__USER__ u', 'a.user_id = u.id']
        ];

        $field = 'a.*,u.user_login,u.user_nickname,u.user_email';

        $category = empty($filter['category']) ? 0 : intval($filter['category']);
        if (!empty($category)) {
            $where['b.category_id'] = ['eq', $category];
            array_push($join, [
                '__PORTAL_CATEGORY_POST__ b', 'a.id = b.post_id'
            ]);
            $field = 'a.*,b.id AS post_category_id,b.list_order,b.category_id,u.user_login,u.user_nickname,u.user_email';
        }

        $startTime = empty($filter['start_time']) ? 0 : strtotime($filter['start_time']);
        $endTime   = empty($filter['end_time']) ? 0 : strtotime($filter['end_time']);
        if (!empty($startTime) && !empty($endTime)) {
            $where['a.published_time'] = [['>= time', $startTime], ['<= time', $endTime]];
        } else {
            if (!empty($startTime)) {
                $where['a.published_time'] = ['>= time', $startTime];
            }
            if (!empty($endTime)) {
                $where['a.published_time'] = ['<= time', $endTime];
            }
        }

        $keyword = empty($filter['keyword']) ? '' : $filter['keyword'];
        if (!empty($keyword)) {
            $where['a.post_title'] = ['like', "%$keyword%"];
        }

        if ($isPage) {
            $where['a.post_type'] = 2;
        } else {
            $where['a.post_type'] = 1;
        }

        $portalPostModel = new PortalPostModel();
        $articles        = $portalPostModel->alias('a')->field($field)
            ->join($join)
            ->where($where)
            ->order('update_time', 'DESC')
            ->paginate(10);

        return $articles;

    }

    /**
     * 已发布文章查询
     * @param  int $postId 文章id
     * @param int $categoryId 分类id
     * @return array|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function publishedArticle($postId, $categoryId = 0)
    {
        $portalPostModel = new PortalPostModel();

        if (empty($categoryId)) {

            $where = [
                'post.post_type'      => 1,
                'post.published_time' => [['< time', time()], ['> time', 0]],
                'post.post_status'    => 1,
                'post.delete_time'    => 0,
                'post.id'             => $postId
            ];

            $article = $portalPostModel->alias('post')->field('post.*')
                ->where($where)
                ->find();
        } else {
            $where = [
                'post.post_type'       => 1,
                'post.published_time'  => [['< time', time()], ['> time', 0]],
                'post.post_status'     => 1,
                'post.delete_time'     => 0,
                'relation.category_id' => $categoryId,
                'relation.post_id'     => $postId
            ];

            $join    = [
                ['__PORTAL_CATEGORY_POST__ relation', 'post.id = relation.post_id']
            ];
            $article = $portalPostModel->alias('post')->field('post.*')
                ->join($join)
                ->where($where)
                ->find();
        }


        return $article;
    }

    /**
     * 上一篇文章
     * @param int $postId 文章id
     * @param int $categoryId 分类id
     * @return array|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function publishedPrevArticle($postId, $categoryId = 0)
    {
        $portalPostModel = new PortalPostModel();

        if (empty($categoryId)) {

            $where = [
                'post.post_type'      => 1,
                'post.published_time' => [['< time', time()], ['> time', 0]],
                'post.post_status'    => 1,
                'post.delete_time'    => 0,
                'post.id '            => ['<', $postId]
            ];

            $article = $portalPostModel->alias('post')->field('post.*')
                ->where($where)
                ->order('id', 'DESC')
                ->find();

        } else {
            $where = [
                'post.post_type'       => 1,
                'post.published_time'  => [['< time', time()], ['> time', 0]],
                'post.post_status'     => 1,
                'post.delete_time'     => 0,
                'relation.category_id' => $categoryId,
                'relation.post_id'     => ['<', $postId]
            ];

            $join    = [
                ['__PORTAL_CATEGORY_POST__ relation', 'post.id = relation.post_id']
            ];
            $article = $portalPostModel->alias('post')->field('post.*')
                ->join($join)
                ->where($where)
                ->order('id', 'DESC')
                ->find();
        }


        return $article;
    }

    /**
     * 下一篇文章
     * @param int $postId 文章id
     * @param int $categoryId 分类id
     * @return array|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function publishedNextArticle($postId, $categoryId = 0)
    {
        $portalPostModel = new PortalPostModel();

        if (empty($categoryId)) {

            $where = [
                'post.post_type'      => 1,
                'post.published_time' => [['< time', time()], ['> time', 0]],
                'post.post_status'    => 1,
                'post.delete_time'    => 0,
                'post.id'             => ['>', $postId]
            ];

            $article = $portalPostModel->alias('post')->field('post.*')
                ->where($where)
                ->order('id', 'ASC')
                ->find();
        } else {
            $where = [
                'post.post_type'       => 1,
                'post.published_time'  => [['< time', time()], ['> time', 0]],
                'post.post_status'     => 1,
                'post.delete_time'     => 0,
                'relation.category_id' => $categoryId,
                'relation.post_id'     => ['>', $postId]
            ];

            $join    = [
                ['__PORTAL_CATEGORY_POST__ relation', 'post.id = relation.post_id']
            ];
            $article = $portalPostModel->alias('post')->field('post.*')
                ->join($join)
                ->where($where)
                ->order('id', 'ASC')
                ->find();
        }


        return $article;
    }

    /**
     * 页面管理查询
     * @param int $pageId 文章id
     * @return array|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function publishedPage($pageId)
    {

        $where = [
            'post_type'      => 2,
            'published_time' => [['< time', time()], ['> time', 0]],
            'post_status'    => 1,
            'delete_time'    => 0,
            'id'             => $pageId
        ];

        $portalPostModel = new PortalPostModel();
        $page            = $portalPostModel
            ->where($where)
            ->find();

        return $page;
    }

}