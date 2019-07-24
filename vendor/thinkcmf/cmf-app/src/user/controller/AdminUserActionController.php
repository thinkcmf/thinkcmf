<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------

namespace app\user\controller;

use app\user\logic\UserActionLogic;
use cmf\controller\AdminBaseController;
use think\Db;

/**
 * Class AdminUserActionController
 * @package app\user\controller
 */
class AdminUserActionController extends AdminBaseController
{

    /**
     * 用户操作管理
     * @adminMenu(
     *     'name'   => '用户操作管理',
     *     'parent' => 'admin/Setting/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '用户操作管理',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $where   = [];
        $request = input('request.');

        if (!empty($request['uid'])) {
            $where['id'] = intval($request['uid']);
        }
        $keywordComplex = [];
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];

            $keywordComplex['user_login']    = ['like', "%$keyword%"];
            $keywordComplex['user_nickname'] = ['like', "%$keyword%"];
            $keywordComplex['user_email']    = ['like', "%$keyword%"];
        }

        $actions = Db::name('user_action')->paginate(20);
        // 获取分页显示
        $page = $actions->render();
        $this->assign('actions', $actions);
        $this->assign('page', $page);
        // 渲染模板输出
        return $this->fetch();
    }

    /**
     * 编辑用户操作
     * @adminMenu(
     *     'name'   => '编辑用户操作',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑用户操作',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        $id     = $this->request->param('id', 0, 'intval');
        $action = Db::name('user_action')->where('id', $id)->find();
        $this->assign($action);

        return $this->fetch();
    }

    /**
     * 编辑用户操作提交
     * @adminMenu(
     *     'name'   => '编辑用户操作提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑用户操作提交',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {
        $id = $this->request->param('id', 0, 'intval');

        $data = $this->request->param();

        Db::name('user_action')->where('id', $id)
            ->strict(false)
            ->field('score,coin,reward_number,cycle_type,cycle_time')
            ->update($data);

        $this->success('保存成功！');
    }

    /**
     * 同步用户操作
     * @adminMenu(
     *     'name'   => '同步用户操作',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '同步用户操作',
     *     'param'  => ''
     * )
     */
    public function sync()
    {

        $apps = cmf_scan_dir(APP_PATH . '*', GLOB_ONLYDIR);

        array_push($apps, 'admin', 'user');

        foreach ($apps as $app) {
            UserActionLogic::importUserActions($app);
        }

        return $this->fetch();
    }


}
