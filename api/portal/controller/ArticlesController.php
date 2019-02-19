<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: pl125 <xskjs888@163.com>
// +----------------------------------------------------------------------

namespace api\portal\controller;

use api\portal\service\PortalPostService;
use api\user\model\UserFavoriteModel;
use api\user\model\UserLikeModel;
use cmf\controller\RestBaseController;
use api\portal\model\PortalPostModel;
use think\Db;

class ArticlesController extends RestBaseController
{
    /**
     * 文章列表
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $params      = $this->request->get();
        $postService = new PortalPostService();
        $data        = $postService->postArticles($params);
        //是否需要关联模型
        if (!$data->isEmpty()) {
            if (!empty($params['relation'])) {

                $allowedRelations = allowed_relations(['user', 'categories'], $params['relation']);

                if (!empty($allowedRelations)) {
                    $data->load('user');
                    $data->append($allowedRelations);
                }
            }
        }
        if (empty($this->apiVersion) || $this->apiVersion == '1.0.0') {
            $response = $data;
        } else {
            $response = ['list' => $data];
        }
        $this->success('请求成功!', $response);
    }

    /**
     * 获取指定的文章
     * @param $id
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function read($id)
    {
        if (intval($id) === 0) {
            $this->error('无效的文章id！');
        } else {
            $postModel = new PortalPostModel();
            $data      = $postModel->where('id', $id)->find();

            if (empty($data)) {
                $this->error('文章不存在！');
            } else {
                $postModel->where('id', $id)->setInc('post_hits');
                $url         = cmf_url('portal/Article/index', ['id' => $id, 'cid' => $data['categories'][0]['id']], true, true);
                $data['url'] = $url;
                $this->success('请求成功!', $data);
            }

        }
    }

    /**
     * 我的文章列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function my()
    {
        $params            = $this->request->get();
        $params['user_id'] = $this->getUserId();

        $postService = new PortalPostService();
        $data        = $postService->postArticles($params);

        if (empty($this->apiVersion) || $this->apiVersion == '1.0.0') {
            $response = [$data];
        } else {
            $response = ['list' => $data];
        }

        $this->success('请求成功!', $response);
    }

    /**
     * 添加文章
     * @throws \think\Exception
     */
    public function save()
    {
        $data            = $this->request->post();
        $data['user_id'] = $this->getUserId();
        $result          = $this->validate($data, 'Articles.article');
        if ($result !== true) {
            $this->error($result);
        }

        if (empty($data['published_time'])) {
            $data['published_time'] = time();
        }
        $postModel = new PortalPostModel();
        $postModel->addArticle($data);
        $this->success('添加成功！');
    }

    /**
     * 更新文章
     * @param $id
     * @throws \think\Exception
     */
    public function update($id)
    {
        $data   = $this->request->put();
        $result = $this->validate($data, 'Articles.article');
        if ($result !== true) {
            $this->error($result);
        }
        $postModel = new PortalPostModel();
        $res       = $postModel->articleFind(['id' => $id, 'user_id' => $this->getUserId()]);
        if (empty($res)) {
            $this->error('文章不存在或者已经删除！');
        }

        $result = $postModel->editArticle($data, $id, $this->getUserId());

        if ($result === false) {
            $this->error('编辑失败！');
        } else {
            $this->success('编辑成功！');
        }
    }

    /**
     * 删除文章
     * @param $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function delete($id)
    {
        if (empty($id)) {
            $this->error('无效的文章id');
        }
        $postModel = new PortalPostModel();
        $result    = $postModel->deleteArticle($id, $this->getUserId());
        if ($result == -1) {
            $this->error('文章已删除');
        }
        if ($result) {
            $this->success('删除成功！');
        } else {
            $this->error('删除失败！');
        }
    }

    /**
     * 批量删除文章
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function deletes()
    {
        $ids = $this->request->post('ids/a');
        if (empty($ids)) {
            $this->error('文章id不能为空');
        }
        $postModel = new PortalPostModel();
        $result    = $postModel->deleteArticle($ids, $this->getUserId());
        if ($result == -1) {
            $this->error('文章已删除');
        }
        if ($result) {
            $this->success('删除成功！');
        } else {
            $this->error('删除失败！');
        }
    }

    /**
     * 搜索查询
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function search()
    {
        $params = $this->request->get();

        if (!empty($params['keyword'])) {
            $postService = new PortalPostService();
            $data        = $postService->postArticles($params);

            if (empty($this->apiVersion) || $this->apiVersion == '1.0.0') {
                $response = $data;
            } else {
                $response = ['list' => $data,];
            }
            $this->success('请求成功!', $response);
        } else {
            $this->error('搜索关键词不能为空！');
        }

    }

    /**
     * 文章点赞
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function doLike()
    {
        $userId    = $this->getUserId();
        $articleId = $this->request->param('id', 0, 'intval');

        $userLikeModel = new UserLikeModel();

        $findLikeCount = $userLikeModel->where([
            'user_id'   => $userId,
            'object_id' => $articleId
        ])->where('table_name', 'portal_post')->count();

        if (empty($findLikeCount)) {
            $postModel = new PortalPostModel();
            $article   = $postModel->where('id', $articleId)->field('id,post_title,post_excerpt,more')->find();

            if (empty($article)) {
                $this->error('文章不存在！');
            }

            Db::startTrans();
            try {
                $postModel->where(['id' => $articleId])->setInc('post_like');
                $thumbnail = empty($article['more']['thumbnail']) ? '' : $article['more']['thumbnail'];
                $userLikeModel->insert([
                    'user_id'     => $userId,
                    'object_id'   => $articleId,
                    'table_name'  => 'portal_post',
                    'title'       => $article['post_title'],
                    'thumbnail'   => $thumbnail,
                    'description' => $article['post_excerpt'],
                    'url'         => json_encode(['action' => 'portal/Article/index', 'param' => ['id' => $articleId, 'cid' => $article['categories'][0]['id']]]),
                    'create_time' => time()
                ]);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                $this->error('点赞失败！');
            }

            $likeCount = $postModel->where('id', $articleId)->value('post_like');
            $this->success("赞好啦！", ['post_like' => $likeCount]);
        } else {
            $this->error("您已赞过啦！");
        }
    }

    /**
     * 取消文章点赞
     */
    public function cancelLike()
    {
        $userId = $this->getUserId();

        $articleId = $this->request->param('id', 0, 'intval');

        $userLikeModel = new UserLikeModel();

        $findLikeCount = $userLikeModel->where([
            'user_id'   => $userId,
            'object_id' => $articleId
        ])->where('table_name', 'portal_post')->count();

        if (!empty($findLikeCount)) {
            $postModel = new PortalPostModel();
            Db::startTrans();
            try {
                $postModel->where(['id' => $articleId])->setDec('post_like');
                $userLikeModel->where([
                    'user_id'   => $userId,
                    'object_id' => $articleId
                ])->where('table_name', 'portal_post')->delete();
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                $this->error('取消点赞失败！');
            }

            $likeCount = $postModel->where('id', $articleId)->value('post_like');
            $this->success("取消点赞成功！", ['post_like' => $likeCount]);
        } else {
            $this->error("您还没赞过！");
        }
    }

    /**
     * 文章收藏
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function doFavorite()
    {
        $userId = $this->getUserId();

        $articleId = $this->request->param('id', 0, 'intval');

        $userFavoriteModel = new UserFavoriteModel();

        $findFavoriteCount = $userFavoriteModel->where([
            'user_id'   => $userId,
            'object_id' => $articleId
        ])->where('table_name', 'portal_post')->count();

        if (empty($findFavoriteCount)) {
            $postModel = new PortalPostModel();
            $article   = $postModel->where(['id' => $articleId])->field('id,post_title,post_excerpt,more')->find();
            if (empty($article)) {
                $this->error('文章不存在！');
            }

            Db::startTrans();
            try {
                $postModel->where(['id' => $articleId])->setInc('post_favorites');
                $thumbnail = empty($article['more']['thumbnail']) ? '' : $article['more']['thumbnail'];
                $userFavoriteModel->insert([
                    'user_id'     => $userId,
                    'object_id'   => $articleId,
                    'table_name'  => 'portal_post',
                    'thumbnail'   => $thumbnail,
                    'title'       => $article['post_title'],
                    'description' => $article['post_excerpt'],
                    'url'         => json_encode(['action' => 'portal/Article/index', 'param' => ['id' => $articleId, 'cid' => $article['categories'][0]['id']]]),
                    'create_time' => time()
                ]);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();

                $this->error('收藏失败！');
            }

            $favoriteCount = $postModel->where('id', $articleId)->value('post_favorites');
            $this->success("收藏好啦！", ['post_favorites' => $favoriteCount]);
        } else {
            $this->error("您已收藏过啦！");
        }
    }

    /**
     * 取消文章收藏
     */
    public function cancelFavorite()
    {
        $userId = $this->getUserId();

        $articleId = $this->request->param('id', 0, 'intval');

        $userFavoriteModel = new UserFavoriteModel();

        $findFavoriteCount = $userFavoriteModel->where([
            'user_id'   => $userId,
            'object_id' => $articleId
        ])->where('table_name', 'portal_post')->count();

        if (!empty($findFavoriteCount)) {
            $postModel = new PortalPostModel();
            Db::startTrans();
            try {
                $postModel->where(['id' => $articleId])->setDec('post_favorites');
                $userFavoriteModel->where([
                    'user_id'   => $userId,
                    'object_id' => $articleId
                ])->where('table_name', 'portal_post')->delete();
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                $this->error('取消失败！');
            }

            $favoriteCount = $postModel->where('id', $articleId)->value('post_favorites');
            $this->success("取消成功！", ['post_favorites' => $favoriteCount]);
        } else {
            $this->error("您还没收藏过！");
        }
    }


    /**
     * 相关文章列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function relatedArticles()
    {
        $articleId  = $this->request->param('id', 0, 'intval');
        $categoryId = Db::name('portal_category_post')->where('post_id', $articleId)->value('category_id');

        $postModel = new PortalPostModel();
        $articles  = $postModel
            ->alias('post')
            ->join('__PORTAL_CATEGORY_POST__ category_post', 'post.id=category_post.post_id')
            ->where(['post.delete_time' => 0, 'post.post_status' => 1, 'category_post.category_id' => $categoryId])
            ->orderRaw('rand()')
            ->limit(5)
            ->select();
        if ($articles->isEmpty()){
            $this->error('没有相关文章！');
        }
        $this->success('success', ['list' => $articles]);
    }
}