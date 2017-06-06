<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use cmf\controller\AdminBaseController;

class StorageController extends AdminBaseController
{

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 文件存储
     * @adminMenu(
     *     'name'   => '文件存储',
     *     'parent' => 'admin/Setting/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '文件存储',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $storage = cmf_get_option('storage');

        if (empty($storage)) {
            $storage['type']     = 'Local';
            $storage['storages'] = ['Local' => ['name' => '本地']];
        } else {
            if (empty($storage['type'])) {
                $storage['type'] = 'Local';
            }

            if (empty($storage['storages']['Local'])) {
                $storage['storages']['Local'] = ['name' => '本地'];
            }
        }

        $this->assign($storage);
        return $this->fetch();
    }

    /**
     * 文件存储
     * @adminMenu(
     *     'name'   => '文件存储设置提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '文件存储设置提交',
     *     'param'  => ''
     * )
     */
    public function settingPost()
    {
        $post = $this->request->post();

        $storage = cmf_get_option('storage');

        $storage['type'] = $post['type'];
        cmf_set_option('storage', $storage);
        $this->success("设置成功！", '');

    }


}