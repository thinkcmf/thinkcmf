<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace api\admin\controller;

use app\admin\logic\PluginLogic;
use app\admin\model\HookPluginModel;
use app\admin\model\PluginModel;
use app\admin\model\RecycleBinModel;
use cmf\controller\RestAdminBaseController;
use OpenApi\Annotations as OA;
use think\facade\Cache;
use think\Validate;

class PluginController extends RestAdminBaseController
{
    /**
     * 插件列表
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/plugins",
     *     summary="插件列表",
     *     description="插件列表",
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "plugins":{
     *                  {"id": 1,"status": 1,"delete_time": 0,"name": "又菜又爱玩","remark": ""}
     *              }
     *          }})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function index()
    {
        $pluginModel = new PluginModel();
        $plugins     = $pluginModel->getList();
        $this->success("success", ['plugins' => $plugins]);
    }

    /**
     * 获取插件配置
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/plugins/{id}/config",
     *     summary="获取插件配置",
     *     description="获取插件配置",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="插件id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "config":{"configxxx":"configxxx_value"}
     *          }})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "not found!","data":""})
     *     ),
     * )
     */
    public function config()
    {
        $id = $this->request->param('id', 0, 'intval');

        $pluginModel = new PluginModel();
        $plugin      = $pluginModel->find($id);

        if (empty($plugin)) {
            $this->error('插件未安装!');
        }

        $plugin = $plugin->toArray();

        $pluginClass = cmf_get_plugin_class($plugin['name']);
        if (!class_exists($pluginClass)) {
            $this->error('插件不存在!');
        }

        $pluginObj = new $pluginClass;
        //$plugin['plugin_path']   = $pluginObj->plugin_path;
        //$plugin['custom_config'] = $pluginObj->custom_config;
        $pluginConfigInDb = $plugin['config'];
        $pluginConfigFile = $pluginObj->getConfigFilePath();
        if (is_file($pluginConfigFile)) {
            $plugin['config'] = include $pluginConfigFile;
        }

        if ($pluginConfigInDb) {
            foreach ($plugin['config'] as $key => $value) {
                if ($value['type'] != 'group') {
                    if (isset($pluginConfigInDb[$key])) {
                        $plugin['config'][$key]['value'] = $pluginConfigInDb[$key];
                    }
                } else {
                    foreach ($value['options'] as $group => $options) {
                        foreach ($options['options'] as $gkey => $value) {
                            if (isset($pluginConfigInDb[$gkey])) {
                                $plugin['config'][$key]['options'][$group]['options'][$gkey]['value'] = $pluginConfigInDb[$gkey];
                            }
                        }
                    }
                }
            }
        }

        $this->success('success', ['config' => $plugin['config']]);
    }

    /**
     * 插件配置提交保存
     * @throws \think\exception\DbException
     * @OA\Put(
     *     tags={"admin"},
     *     path="/admin/plugins/{id}/config",
     *     summary="插件配置提交保存",
     *     description="插件配置提交保存",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="插件id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/AdminPluginConfigPutRequestForm")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AdminPluginConfigPutRequest")
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "保存成功","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function configPut($id)
    {
        $id = $this->request->param('id', 0, 'intval');

        $pluginModel = new PluginModel();
        $plugin      = $pluginModel->find($id)->toArray();

        if (!$plugin) {
            $this->error('插件未安装!');
        }

        $pluginClass = cmf_get_plugin_class($plugin['name']);
        if (!class_exists($pluginClass)) {
            $this->error('插件不存在!');
        }

        $pluginObj = new $pluginClass;
        //$plugin['plugin_path']   = $pluginObj->plugin_path;
        //$plugin['custom_config'] = $pluginObj->custom_config;
        $pluginConfigInDb = $plugin['config'];
        if (empty($pluginConfigInDb)) {
            $this->error('插件无配置！');
        }
        $plugin['config'] = include $pluginObj->getConfigFilePath();

        $rules    = [];
        $messages = [];

        foreach ($plugin['config'] as $key => $value) {
            if ($value['type'] != 'group') {
                if (isset($value['rule'])) {
                    $rules[$key] = $this->_parseRules($value['rule']);
                }

                if (isset($value['message'])) {
                    foreach ($value['message'] as $rule => $msg) {
                        $messages[$key . '.' . $rule] = $msg;
                    }
                }

            } else {
                foreach ($value['options'] as $group => $options) {
                    foreach ($options['options'] as $gkey => $value) {
                        if (isset($value['rule'])) {
                            $rules[$gkey] = $this->_parseRules($value['rule']);
                        }

                        if (isset($value['message'])) {
                            foreach ($value['message'] as $rule => $msg) {
                                $messages[$gkey . '.' . $rule] = $msg;
                            }
                        }
                    }
                }
            }
        }

        $config = $this->request->param('config/a');

        $validate = new Validate();
        $validate->rule($rules);
        $validate->message($messages);
        $result = $validate->check($config);
        if ($result !== true) {
            $this->error($validate->getError());
        }

        $pluginModel = PluginModel::where('id', $id)->find();
        $pluginModel->save(['config' => $config]);
        cmf_clear_cache();
        $this->success(lang('EDIT_SUCCESS'), '');
    }

    /**
     * 安装插件
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/plugins/{name}",
     *     summary="安装插件",
     *     description="安装插件",
     *     @OA\Parameter(
     *         name="name",
     *         in="path",
     *         description="插件名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "安装成功","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function install()
    {
        if ($this->request->isPost()) {
            $pluginName = $this->request->param('name', '', 'trim');
            $result     = PluginLogic::install($pluginName);

            if ($result !== true) {
                $this->error($result);
            }

            $this->success(lang('Installed successfully'));
        }
    }

    /**
     * 解析插件配置验证规则
     * @param $rules
     * @return array
     */
    private function _parseRules($rules)
    {
        $newRules = [];

        $simpleRules = [
            'require', 'number',
            'integer', 'float', 'boolean', 'email',
            'array', 'accepted', 'date', 'alpha',
            'alphaNum', 'alphaDash', 'activeUrl',
            'url', 'ip'];
        foreach ($rules as $key => $rule) {
            if (in_array($key, $simpleRules) && $rule) {
                array_push($newRules, $key);
            }
        }

        return $newRules;
    }

    /**
     * 更新插件
     * @throws \think\exception\DbException
     * @OA\Put(
     *     tags={"admin"},
     *     path="/admin/plugins/{name}",
     *     summary="更新插件",
     *     description="更新插件",
     *     @OA\Parameter(
     *         name="name",
     *         in="path",
     *         description="插件名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "更新成功","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function update()
    {
        if ($this->request->isPut()) {
            $pluginName = $this->request->param('name', '', 'trim');
            $result     = PluginLogic::update($pluginName);

            if ($result !== true) {
                $this->error($result);
            }
            $this->success(lang('Updated successfully'));
        }
    }

    /**
     * 卸载插件
     * @throws \think\exception\DbException
     * @OA\Delete(
     *     tags={"admin"},
     *     path="/admin/plugins/{id}",
     *     summary="卸载插件",
     *     description="卸载插件",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="插件id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "卸载成功!","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error","data":""})
     *     ),
     * )
     */
    public function uninstall()
    {
        if ($this->request->isDelete()) {
            $pluginModel = new PluginModel();
            $id          = $this->request->param('id', 0, 'intval');

            $result = $pluginModel->uninstall($id);

            if ($result !== true) {
                $this->error(lang('Uninstall failed'));
            }

            Cache::clear('init_hook_plugins');
            Cache::clear('admin_menus');// 删除后台菜单缓存

            $this->success(lang('Uninstall successful'));
        }
    }

    /**
     * 设置插件启用状态
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/plugins/{id}/status/{status}",
     *     summary="设置插件启用状态",
     *     description="设置插件启用状态",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="插件ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="path",
     *         description="插件启用状态,0:禁用;1:启用",
     *         example="1",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "操作成功!","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "插件不存在！","data":""})
     *     ),
     * )
     */
    public function status()
    {
        $id          = $this->request->param('id', 0, 'intval');
        $status      = $this->request->param('status', 1, 'intval');
        $pluginModel = PluginModel::find($id);

        if (empty($pluginModel)) {
            $this->error('插件不存在！');
        }

        $pluginModel->startTrans();
        try {
            $status = empty($status) ? 0 : 1;
            $pluginModel->save(['status' => $status]);
            $hookPluginModel = new HookPluginModel();
            $hookPluginModel->where(['plugin' => $pluginModel->name])->update(['status' => $status]);

            $pluginModel->commit();

        } catch (\Exception $e) {

            $pluginModel->rollback();

            $this->error('操作失败！');

        }

        Cache::clear('init_hook_plugins');

        $this->success('操作成功！');
    }

}
