<?php
namespace app\portal\controller;

use cmf\controller\HomeBaseController;
use app\portal\service\PostService;

class PageController extends HomeBaseController
{
    public function index()
    {
        $postService = new PostService();
        $pageId      = $this->request->param('id', 0, 'intval');
        $page        = $postService->publishedPage($pageId);

        if (empty($page)) {
            abort(404, ' 页面不存在!');
        }

        $more = json_decode($page['more'], true);

        $page['more'] = $more;

        $this->assign('page', $page);

        $tplName = empty($more['template']) ? 'page' : $more['template'];

        return $this->fetch("/$tplName");
    }

}
