<?php

namespace app\admin\controller;

use app\admin\model\RouteModel;
use cmf\controller\AdminBaseController;
use think\Db;

class RouteController extends AdminBaseController
{

    public function _initialize()
    {
        parent::_initialize();
    }

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
        $routeModel = new RouteModel();
        $routes = Db::name('route')->order("list_order asc")->select();
        $routeModel->getRoutes(true);
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
        $data       = $this->request->param();
        $routeModel = new RouteModel();
        $result     = $routeModel->validate(true)->allowField(true)->save($data);
        if ($result === false) {
            $this->error($routeModel->getError());
        }

        $this->success("添加成功！", url("Route/index", ['id' => $routeModel->id]));
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
        $route = Db::name('route')->where(['id' => $id])->find();
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
        $data       = $this->request->param();
        $routeModel = new RouteModel();
        $result     = $routeModel->validate(true)->allowField(true)->isUpdate(true)->save($data);
        if ($result === false) {
            $this->error($routeModel->getError());
        }

        $this->success("保存成功！", url("Route/index"));
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
        $id = $this->request->param('id', 0, 'intval');
        RouteModel::destroy($id);

        $this->success("删除成功！");
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
        $id             = $this->request->param("id", 0, 'intval');
        $data           = [];
        $data['status'] = 0;
        $data['id']     = $id;
        $routeModel     = new RouteModel();

        $routeModel->isUpdate(true)->save($data);
        $this->success("禁用成功！");
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
        $id             = $this->request->param("id", 0, 'intval');
        $data           = [];
        $data['status'] = 1;
        $data['id']     = $id;
        $routeModel     = new RouteModel();

        $routeModel->isUpdate(true)->save($data);
        $this->success("启用成功！");
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

}