<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\user\controller;

use think\Db;
use app\portal\model\PortalPostModel;
use cmf\controller\HomeBaseController;

class ArticlesController extends HomeBaseController
{
    public function index()
    {
        //获取登录会员信息
        $user = cmf_get_current_user();
        $this->assign('user_id', $user['id']);
        return $this->fetch();
    }

    public function add()
    {

        return $this->fetch();
    }

    public function addPost()
    {
        if ($this->request->isPost()) {
            $data   = $this->request->param();
            $post   = $data['post'];
            $result = $this->validate($post, 'UserArticles');

            if ($result !== true) {
                $this->error($result);
            }

            $portalPostModel = new PortalPostModel();

            if (!empty($data['photo_names']) && !empty($data['photo_urls'])) {
                $data['post']['more']['photos'] = [];
                foreach ($data['photo_urls'] as $key => $url) {
                    $photoUrl = cmf_asset_relative_url($url);
                    array_push($data['post']['more']['photos'], ["url" => $photoUrl, "name" => $data['photo_names'][$key]]);
                }
            }

            if (!empty($data['file_names']) && !empty($data['file_urls'])) {
                $data['post']['more']['files'] = [];
                foreach ($data['file_urls'] as $key => $url) {
                    $fileUrl = cmf_asset_relative_url($url);
                    array_push($data['post']['more']['files'], ["url" => $fileUrl, "name" => $data['file_names'][$key]]);
                }
            }

            $portalPostModel->adminAddArticle($data['post'], $data['post']['categories']);

            $this->success('添加成功!', url('Articles/add', ['id' => $portalPostModel->id]));
        }
    }
}