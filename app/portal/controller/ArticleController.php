<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\portal\controller;

use cmf\controller\HomeBaseController;
use app\portal\model\PortalCategoryModel;
use app\portal\service\PostService;
use app\portal\model\PortalPostModel;
use think\Db;

class ArticleController extends HomeBaseController
{
    public function index()
    {

        $portalCategoryModel = new PortalCategoryModel();
        $postService         = new PostService();

        $articleId  = $this->request->param('id', 0, 'intval');
        $categoryId = $this->request->param('cid', 0, 'intval');
        $article    = $postService->publishedArticle($articleId, $categoryId);

        if (empty($articleId)) {
            abort(404, '文章不存在!');
        }

        //TODO 上一篇,下一篇

        $tplName = 'article';

        if (!empty($categoryId)) {

            $category = $portalCategoryModel->where('id', $categoryId)->where('status', 1)->find();

            if (empty($category)) {
                abort(404, '文章不存在!');
            }

            $this->assign('category', $category);

            $tplName = empty($category["one_tpl"]) ? $tplName : $category["one_tpl"];
        }

        Db::name('portal_post')->where(['id' => $articleId])->setInc('post_hits');

        $this->assign('article', $article);

        $tplName = empty($article['more']['template']) ? $tplName : $article['more']['template'];

        return $this->fetch("/$tplName");
    }

    // 文章点赞
    public function doLike()
    {
        $articleId = $this->request->param('id', 0, 'intval');

        Db::name('portal_post')->where(['id' => $articleId])->setInc('post_like');

        $this->success("赞好啦！");

//        $canLike = cmf_check_user_action("posts$id", 1);
//
//        if ($canLike) {
//            $postsModel->save(["id" => $id, "post_like" => ["exp", "post_like+1"]]);
//            $this->success("赞好啦！");
//        } else {
//            $this->error("您已赞过啦！");
//        }
    }


}
