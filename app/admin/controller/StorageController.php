<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
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

        $supportStorages = ["Local", "Qiniu"];
        $type            = $post['type'];
        if (in_array($type, $supportStorages)) {

            if ($type == 'Qiniu') {
                $result = $this->validate($post['Qiniu'], 'StorageQiniu');

                if ($result !== true) {
                    $this->error($result);
                }
            }

            cmf_set_option('storage', $post);
            $this->success("设置成功！", '');
        } else {
            $this->error("文件存储不存在！", '');
        }

    }


}