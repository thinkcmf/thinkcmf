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

        $portalPostModel         = new PortalPostModel();
        $data['post']['more']    = json_encode($data['more']);

        $portalPostModel->adminAddPage($data['post']);

        $this->success(lang('ADD_SUCCESS'));


    }

    // 编辑页面
    public function edit()
    {
        $id = $this->request->param('id', 0, 'intval');

        $portalPostModel = new PortalPostModel();
        $post            = $portalPostModel->where('id', $id)->find();
        $more            = json_decode($post['more'],true);

        $this->assign('more', $more);
        $this->assign('post', $post);

        return $this->fetch();
    }

    // 编辑页面提交保存
    public function editPost()
    {

        $data = $this->request->param();

        $portalPostModel = new PortalPostModel();


        $data['post']['more']    = json_encode($data['more']);

        $portalPostModel->adminEditPage($data['post']);

        $this->success(lang('SAVE_SUCCESS'));

    }


    /**
     *
     * 页面管理删除方法
     * @copyright [copyright]
     * @license   [license]
     * @version   [version]
     * @author    iyting@foxmail.com
     * @time      2017-03-28T11:02:47+0800
     * @return    [type]
     */
    public function delete()
    {

        $portalPostModel = new PortalPostModel();
        $data = $this->request->param();


        $result = $portalPostModel->adminDeletePage($data);
        if( $result )
        {

            $this->success(lang('DELETE_SUCCESS'));
        } else {
            $this->error(lang('DELETE_FAILED'));
        }

    }
    /**
     * 后台页面回收站
     * @copyright [copyright]
     * @license   [license]
     * @version   [version]
     * @author
     * @time      2017-03-31T13:45:31+0800
     * @return    [type]
     */
    public function recyclebin(){

        //$this->_lists(array('post_status'=>array('eq',3)));


        return $this->fetch();
    }

}
