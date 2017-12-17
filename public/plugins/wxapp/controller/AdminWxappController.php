<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace plugins\wxapp\controller; //Demo插件英文名，改成你的插件英文就行了

use think\Db;
use cmf\controller\PluginBaseController;

class AdminWxappController extends PluginBaseController
{

    function _initialize()
    {
        $adminId = cmf_get_current_admin_id();//获取后台管理员id，可判断是否登录
        if (!empty($adminId)) {
            $this->assign("admin_id", $adminId);
        } else {
            $this->error('未登录');
        }
    }

    public function add()
    {

        return $this->fetch();
    }

    public function addPost()
    {
        $data = $this->request->param();

        $result = $this->validate($data, "AdminWxapp");

        if ($result !== true) {
            $this->error($result);
        }

        $wxappSettings = cmf_get_option('wxapp_settings');

        if (!empty($data['is_default'])) {
            $wxappSettings['default'] = $data;
        }

        unset($data['is_default']);

        $wxappSettings['wxapps'][$data['app_id']] = $data;

        cmf_set_option('wxapp_settings', $wxappSettings);

        $this->success('添加成功！', cmf_plugin_url('Wxapp://AdminWxapp/edit', ['id' => $data['app_id']]));


    }

    public function edit()
    {
        $appId = $this->request->param('id');

        $wxappSettings = cmf_get_option('wxapp_settings');

        if (!empty($wxappSettings['wxapps'][$appId])) {
            $this->assign('wxapp', $wxappSettings['wxapps'][$appId]);
        }

        $defaultWxapp = empty($wxappSettings['default']) ? [] : $wxappSettings['default'];

        $this->assign('default_wxapp', $defaultWxapp);

        return $this->fetch();
    }

    public function editPost()
    {
        $data = $this->request->param();

        $result = $this->validate($data, "AdminWxapp");

        if ($result !== true) {
            $this->error($result);
        }

        $wxappSettings = cmf_get_option('wxapp_settings');

        if (!empty($data['is_default'])) {
            $wxappSettings['default'] = $data;
        }

        unset($data['is_default']);

        $wxappSettings['wxapps'][$data['app_id']] = $data;

        cmf_set_option('wxapp_settings', $wxappSettings);

        $this->success('保存成功！');
    }

    public function delete()
    {
        $appId = $this->request->param('id');

        $wxappSettings = cmf_get_option('wxapp_settings');

        $defaultWxapp = empty($wxappSettings['default']) ? [] : $wxappSettings['default'];

        if (!empty($defaultWxapp['app_id']) && $appId == $defaultWxapp['app_id']) {
            $this->error(' 默认小程序无法删除！');
        }

        unset($wxappSettings['wxapps'][$appId]);

        cmf_set_option('wxapp_settings', $wxappSettings);

        $this->success('删除成功！');
    }

}
