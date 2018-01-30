<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace plugins\wxapp\controller; //Demo插件英文名，改成你的插件英文就行了

use cmf\controller\PluginAdminBaseController;

class AdminWxappController extends PluginAdminBaseController
{

    /**
     * 添加小程序
     * @adminMenu(
     *     'name'   => '添加小程序',
     *     'parent' => 'AdminIndex/index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加小程序',
     *     'param'  => ''
     * )
     */
    public function add()
    {

        return $this->fetch();
    }

    /**
     * 添加小程序提交保存
     * @adminMenu(
     *     'name'   => '添加小程序提交保存',
     *     'parent' => 'AdminIndex/index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加小程序提交保存',
     *     'param'  => ''
     * )
     */
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

    /**
     * 编辑小程序
     * @adminMenu(
     *     'name'   => '编辑小程序',
     *     'parent' => 'AdminIndex/index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑小程序',
     *     'param'  => ''
     * )
     */
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

    /**
     * 编辑小程序提交保存
     * @adminMenu(
     *     'name'   => '编辑小程序提交保存',
     *     'parent' => 'AdminIndex/index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑小程序',
     *     'param'  => ''
     * )
     */
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

    /**
     * 删除小程序
     * @adminMenu(
     *     'name'   => '删除小程序',
     *     'parent' => 'AdminIndex/index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除小程序',
     *     'param'  => ''
     * )
     */
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
