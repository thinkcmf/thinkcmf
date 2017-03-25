<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\portal\controller;

use cmf\controller\AdminBaseController;
use app\portal\model\PortalPostModel;
use app\portal\service\PostService;
use think\Db;

class AdminPageController extends AdminBaseController
{

    // 页面列表
    public function index()
    {
        $param = $this->request->param();

        $postService = new PostService();
        $data        = $postService->adminPageList($param);

        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');
        $this->assign('pages', $data->items());
        $this->assign('page', $data->render());

        return $this->fetch();
    }

    // 添加页面
    public function add()
    {
        return $this->fetch();
    }

    // 添加页面提交保存
    public function addPost()
    {

        $data = $this->request->param();

        $portalPostModel = new PortalPostModel();

        $portalPostModel->adminAddPage($data['post']);

        $this->success('添加成功!');

    }

    // 编辑页面
    public function edit()
    {
        $id = $this->request->param('id', 0, 'intval');

        $portalPostModel = new PortalPostModel();
        $post            = $portalPostModel->where('id', $id)->find();

        $this->assign('post', $post);

        return $this->fetch();
    }

    // 编辑页面提交保存
    public function editPost()
    {

        $data = $this->request->param();

        $portalPostModel = new PortalPostModel();

        $portalPostModel->adminEditPost($data['post']);

        $this->success('保存成功!');

    }

    // 页面删除
    public function delete()
    {
        $param           = $this->request->param();
        $portalPostModel = new PortalPostModel();

        if (isset($param['id'])) {
            $id  = $this->request->param('id', 0, 'intval');
            $res = $portalPostModel->where(['id' => $id])->find();

            $data   = [
                'object_id'   => $res['id'],
                'create_time' => time(),
                'table_name'  => 'portal_post',
                'name'        => $res['post_title'],
                'data'        => $res->tojson()
            ];
            $result = $portalPostModel
                ->where(['id' => $id])
                ->update(['delete_time' => time()]);
            if ($result) {
                Db::name('recycleBin')->insert($data);
                $this->success("删除成功！");
            }


        }

        if (isset($param['ids'])) {
            $ids = $this->request->param('ids/a');

            $portalPostModel->where(['id' => ['in', $ids]])->update(['post_status' => 3, 'delete_time' => time()]);

            $this->success("删除成功！");

        }
    }
}
