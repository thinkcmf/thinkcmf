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

    // 文件存储设置
    public function index()
    {
        $this->assign(cmf_get_option('storage'));
        return $this->fetch();
    }

    // 文件存储设置提交
    public function settingPost()
    {
        if (IS_POST) {

            $supportStorages = ["Local", "Qiniu"];
            $type            = I('post.type');
            $post            = I('post.');
            if (in_array($type, $supportStorages)) {
                $result = sp_set_cmf_setting(['storage' => $post]);
                if ($result !== false) {
                    unset($post[$type]['setting']);
                    sp_set_dynamic_config(["FILE_UPLOAD_TYPE" => $type, "UPLOAD_TYPE_CONFIG" => $post[$type]]);
                    $this->success("设置成功！");
                } else {
                    $this->error("设置出错！");
                }
            } else {
                $this->error("文件存储类型不存在！");
            }

        }
    }


}