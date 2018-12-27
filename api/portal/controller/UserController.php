<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: wuwu <15093565100@163.com>
// +----------------------------------------------------------------------
namespace api\portal\controller;

use api\portal\model\PortalPostModel;
use cmf\controller\RestBaseController;

class UserController extends RestBaseController
{
    /**
     * 会员文章列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function articles()
    {
        $userId = $this->request->param('user_id', 0, 'intval');

        if (empty($userId)) {
            $this->error('用户id不能空！');
        }

        $data            = $this->request->param();
        $portalPostModel = new PortalPostModel();
        $articles        = $portalPostModel->setCondition($data)->where(['user_id' => $userId])->select();

        if (count($articles) == 0) {
            $this->error('没有数据');
        } else {
            $this->success('ok', ['list' => $articles]);
        }
    }

}
