<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\RouteModel;
use cmf\controller\AdminBaseController;

class RouteController extends AdminBaseController
{

    /**
     * 路由规则列表
     * @adminMenu(
     *     'name'   => 'URL美化',
     *     'parent' => 'admin/Setting/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => 'URL规则管理',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        global $CMF_GV_routes;
        $routeModel = new RouteModel();
        $routes     = RouteModel::order("list_order asc")->select();
        $routeModel->getRoutes(true);
        unset($CMF_GV_routes);
        $this->assign("routes", $routes);
        return $this->fetch();
    }

    /**
     * 添加路由规则
     * @adminMenu(
     *     'name'   => '添加路由规则',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加路由规则',
     *     'param'  => ''
     * )
     */
    public function add()
    {
        return $this->fetch();
    }

    /**
     * 添加路由规则提交
     * @adminMenu(
     *     'name'   => '添加路由规则提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加路由规则提交',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        if ($this->request->isPost()) {
            $data       = $this->request->param();
            $routeModel = new RouteModel();
            $result     = $this->validate($data, 'Route');
            if ($result !== true) {
                $this->error($result);
            }
            $routeModel->save($data);

            $this->success("添加成功！", url("Route/index", ['id' => $routeModel->id]));
        }
    }

    /**
     * 路由规则编辑
     * @adminMenu(
     *     'name'   => '路由规则编辑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '路由规则编辑',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        $id    = $this->request->param("id", 0, 'intval');
        $route = RouteModel::where('id', $id)->find()->toArray();
        $this->assign($route);
        return $this->fetch();
    }

    /**
     * 路由规则编辑提交
     * @adminMenu(
     *     'name'   => '路由规则编辑提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '路由规则编辑提交',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {
        if ($this->request->isPost()) {
            $data       = $this->request->param();
            $routeModel = new RouteModel();
            $result     = $this->validate($data, 'Route');
            if ($result !== true) {
                $this->error($result);
            }
            $routeModel->where('id', $data['id'])->update($data);

            $this->success("保存成功！", url("Route/index"));
        }
    }

    /**
     * 路由规则删除
     * @adminMenu(
     *     'name'   => '路由规则删除',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '路由规则删除',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        if ($this->request->isPost()) {
            $id = $this->request->param('id', 0, 'intval');
            RouteModel::destroy($id);

            $this->success("删除成功！");
        }
    }

    /**
     * 路由规则禁用
     * @adminMenu(
     *     'name'   => '路由规则禁用',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '路由规则禁用',
     *     'param'  => ''
     * )
     */
    public function ban()
    {
        if ($this->request->isPost()) {
            $id             = $this->request->param("id", 0, 'intval');
            $data           = [];
            $data['status'] = 0;
            $data['id']     = $id;
            $routeModel     = new RouteModel();

            $routeModel->where('id', $data['id'])->update($data);
            $this->success("禁用成功！");
        }
    }

    /**
     * 路由规则启用
     * @adminMenu(
     *     'name'   => '路由规则启用',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '路由规则启用',
     *     'param'  => ''
     * )
     */
    public function open()
    {
        if ($this->request->isPost()) {
            $id             = $this->request->param("id", 0, 'intval');
            $data           = [];
            $data['status'] = 1;
            $data['id']     = $id;
            $routeModel     = new RouteModel();

            $routeModel->where('id', $data['id'])->update($data);
            $this->success("启用成功！");
        }
    }

    /**
     * 路由规则排序
     * @adminMenu(
     *     'name'   => '路由规则排序',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '路由规则排序',
     *     'param'  => ''
     * )
     */
    public function listOrder()
    {
        $routeModel = new RouteModel();
        parent::listOrders($routeModel);
        $this->success("排序更新成功！");
    }

    /**
     * 选择 URL
     * @adminMenu(
     *     'name'   => '选择URL',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '选择URL',
     *     'param'  => ''
     * )
     */
    public function select()
    {
        $routeModel = new RouteModel();
        $urls       = $routeModel->getAppUrls();

        $this->assign('urls', $urls);
        return $this->fetch();
    }

    function _suggest_url($action, $url)
    {
        $actionArr = explode('/', $action);

        $params = array_keys($url['vars']);

        $urlDepr1Params = [];

        $urlDepr2Params = [];

        if (!empty($params)) {

            foreach ($params as $param) {
                if (empty($url['vars'][$param]['require'])) {
                    array_push($urlDepr1Params, "[:$param]");
                } else {
                    array_push($urlDepr1Params, ":$param");
                }

                array_push($urlDepr2Params, htmlspecialchars('<') . $param . htmlspecialchars('>'));
            }

        }

        if ($actionArr[2] == 'index') {
            $actionArr[1] = cmf_parse_name($actionArr[1]);
            return empty($params) ? $actionArr[1] . '$' : ($actionArr[1] . '/' . implode('/', $urlDepr1Params) /*. '或' . $actionArr[1] . '-' . implode('-', $urlDepr2Params)*/);
        } else {
            $actionArr[2] = cmf_parse_name($actionArr[2]);
            return empty($params) ? $actionArr[2] . '$' : ($actionArr[2] . '/' . implode('/', $urlDepr1Params) /*. '或' . $actionArr[2] . '-' . implode('-', $urlDepr2Params)*/);
        }

    }

    function _url_vars($url)
    {
        if (!empty($url['vars'])) {
            return implode(',', array_keys($url['vars']));
        }

        return '';
    }

}
