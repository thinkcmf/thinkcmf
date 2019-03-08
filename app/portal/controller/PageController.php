<?php

// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------

namespace app\portal\controller;

use app\portal\service\PostService;
use cmf\controller\HomeBaseController;

class PageController extends HomeBaseController
{
    /**
     * 页面管理.
     *
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     *
     * @return mixed
     */
    public function index()
    {
        $postService = new PostService();
        $pageId = $this->request->param('id', 0, 'intval');
        $page = $postService->publishedPage($pageId);

        if (empty($page)) {
            abort(404, ' 页面不存在!');
        }

        $this->assign('page', $page);

        $more = $page['more'];

        $tplName = empty($more['template']) ? 'page' : $more['template'];

        return $this->fetch("/$tplName");
    }
}
