<?php
namespace app\portal\controller;

use cmf\controller\HomeBaseController;
use app\portal\model\PortalCategoryModel;
use app\portal\service\PostService;

class ArticleController extends HomeBaseController
{
    public function index()
    {

        $portalCategoryModel = new PortalCategoryModel();
        $postService         = new PostService();

        $articleId  = $this->request->param('id', 0, 'intval');
        $categoryId = $this->request->param('cid', 0, 'intval');
        $article    = $postService->publishedArticle($articleId, $categoryId);

        if (empty($article)) {
            abort(404, '文章不存在!');
        }

        //TODO 上一篇,下一篇

        $category = $portalCategoryModel->where('id', $categoryId)->where('status', 1)->find();

        if (empty($category)) {
            abort(404, '文章不存在!');
        }

        $more = json_decode($article['more'], true);

        $article['more'] = $more;

        $this->assign('article', $article);
        $this->assign('category', $category);

        $tplName = empty($category["one_tpl"]) ? 'article' : $category["one_tpl"];
        $tplName = empty($more['template']) ? $tplName : $more['template'];

        return $this->fetch("/$tplName");
    }
}
