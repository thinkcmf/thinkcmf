<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace api\admin\controller;

use app\admin\model\AdminApiModel;
use app\admin\model\AuthAccessModel;
use app\admin\model\RoleModel;
use app\admin\model\RoleUserModel;
use cmf\controller\RestAdminBaseController;
use think\facade\Cache;
use app\admin\model\AdminMenuModel;

class RoleController extends RestAdminBaseController
{
    /**
     * 角色列表
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/roles",
     *     summary="角色列表",
     *     description="角色列表",
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "list":{
     *                  {"name": "财务","type": "1","remark": "角色描述","status": "1","id": 3}
     *              },"total":1
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
        $roles = RoleModel::order(["list_order" => "ASC", "id" => "DESC"])->select();
        $this->success('success', ['list' => $roles, 'total' => $roles->count()]);
    }

    /**
     * 添加角色
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/roles",
     *     summary="添加角色",
     *     description="添加角色",
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/AdminRoleSaveRequest")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AdminRoleSaveRequest")
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "item":{"name": "财务","type": "1","remark": "角色描述","status": "1","id": 3}
     *          }})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function save()
    {
        if ($this->request->isPost()) {
            $data   = $this->request->param();
            $result = $this->validate($data, 'role');
            if ($result !== true) {
                // 验证失败 输出错误信息
                $this->error($result);
            } else {
                $result = RoleModel::create($data);
                if ($result) {
                    $this->success(lang('ADD_SUCCESS'), ['item' => $result]);
                } else {
                    $this->error(lang('ADD_FAILED'));
                }

            }
        }
    }

    /**
     * 获取角色信息
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/roles/{id}",
     *     summary="获取角色信息",
     *     description="获取角色信息",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="角色id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "item":{"name": "财务","type": "1","remark": "角色描述","status": "1","id": 3}
     *          }})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "not found!","data":""})
     *     ),
     * )
     */
    public function read($id)
    {
        $id   = $this->request->param("id", 0, 'intval');
        $data = RoleModel::where("id", $id)->find();
        if (!$data) {
            $this->error("该角色不存在！");
        }
        $this->success('success', ['item' => $data]);
    }

    /**
     * 编辑角色
     * @throws \think\exception\DbException
     * @OA\Put(
     *     tags={"admin"},
     *     path="/admin/roles/{id}",
     *     summary="编辑角色",
     *     description="编辑角色",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="角色id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/AdminRoleSaveRequest")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AdminRoleSaveRequest")
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
    public function update($id)
    {
        $id = $this->request->param("id", 0, 'intval');
        if ($id == 1) {
            $this->error("超级管理员角色不能被修改！");
        }
        if ($this->request->isPut()) {
            $data   = $this->request->param();
            $result = $this->validate($data, 'role');
            if ($result !== true) {
                // 验证失败 输出错误信息
                $this->error($result);
            } else {
                $role = RoleModel::find($id);
                if (empty($role)) {
                    $this->error('角色不存在!');
                }

                $role->save($data);
                $this->success(lang('EDIT_SUCCESS'));
            }
        }
    }

    /**
     * 删除角色
     * @throws \think\exception\DbException
     * @OA\Delete(
     *     tags={"admin"},
     *     path="/admin/roles/{id}",
     *     summary="删除角色",
     *     description="删除角色",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="角色id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "删除成功!","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error","data":""})
     *     ),
     * )
     */
    public function delete($id)
    {
        $id = $this->request->param("id", 0, 'intval');
        if ($id == 1) {
            $this->error("超级管理员角色不能被删除！");
        }
        $count = RoleUserModel::where('role_id', $id)->count();
        if ($count > 0) {
            $this->error("该角色已经有用户！");
        } else {
            $status = RoleModel::destroy($id);
            if (!empty($status)) {
                $this->success(lang('DELETE_SUCCESS'));
            } else {
                $this->error(lang('DELETE_FAILED'));
            }
        }
    }

    /**
     * 获取角色后台菜单授权信息
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/roles/{id}/authorize",
     *     summary="获取角色后台菜单授权信息",
     *     description="获取角色后台菜单授权信息",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="角色id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "list":{"1":{"id": 1,"parent_id": 0,"type": 0,"status": 1,"list_order": 30,"app": "portal",
     *                      "controller": "AdminIndex","action": "default","param": "",
     *                      "name": "门户管理","icon": "th",
     *                      "remark": "门户管理","checked": 0}}
     *          }})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "not found!","data":""})
     *     ),
     * )
     */
    public function authorize()
    {
        $adminMenuModel = new AdminMenuModel();
        //角色ID
        $roleId = $this->request->param("id", 0, 'intval');
        if (empty($roleId)) {
            $this->error("参数错误！");
        }

        $result        = $adminMenuModel->menuCache();
        $privilegeData = AuthAccessModel::where("role_id", $roleId)->column("rule_name");//获取权限表数据

        foreach ($result as $key => $m) {
            $result[$key]['checked'] = ($this->_isChecked($m, $privilegeData)) ? 1 : 0;
        }

        $this->success('success', ['list' => $result, 'total' => count($result)]);
    }

    /**
     * 提交角色后台菜单授权信息
     * @throws \think\exception\DbException
     * @OA\Put(
     *     tags={"admin"},
     *     path="/admin/roles/{id}/authorize",
     *     summary="提交角色后台菜单授权信息",
     *     description="提交角色后台菜单授权信息",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="角色id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/IdsRequestForm")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/IdsRequest")
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "授权成功","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "not found!","data":""})
     *     ),
     * )
     */
    public function authorizePut()
    {
        if ($this->request->isPut()) {
            $roleId = $this->request->param("id", 0, 'intval');
            if (!$roleId) {
                $this->error("需要授权的角色不存在！");
            } else {
                $role = RoleModel::find($roleId);
                if (empty($role)) {
                    $this->error("需要授权的角色不存在！");
                }
            }
            $menuIds = $this->request->param('ids/a');
            if (is_array($menuIds) && count($menuIds) > 0) {

                AuthAccessModel::where(["role_id" => $roleId, 'type' => 'admin_url'])->delete();
                foreach ($menuIds as $menuId) {
                    $menu = AdminMenuModel::where("id", $menuId)->field("app,controller,action")->find();
                    if ($menu) {
                        $app    = $menu['app'];
                        $model  = $menu['controller'];
                        $action = $menu['action'];
                        $name   = strtolower("$app/$model/$action");
                        AuthAccessModel::insert(["role_id" => $roleId, "rule_name" => $name, 'type' => 'admin_url']);
                    }
                }

                Cache::clear('admin_menus');// 删除后台菜单缓存

                $this->success("授权成功！");
            } else {
                //当没有数据时，清除当前角色授权
                AuthAccessModel::where("role_id", $roleId)->where('type', 'admin_url')->delete();
                $this->error("没有接收到数据，执行清除授权成功！");
            }
        }
    }

    /**
     * 获取角色后台API授权信息
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/roles/{id}/api/authorize",
     *     summary="获取角色授权信息",
     *     description="获取角色授权信息",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="角色id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "list":{"id": 1,"parent_id": 0,"type": 1,"url": "POST|admin/public/login","name": "后台管理员登录",
     *     "tags": "admin","remark": "后台管理员登录(请先使用原来登录页面登录，登录获取token后再使用后台API)","checked": 1},
     *              "total":1
     *          }})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "not found!","data":""})
     *     ),
     * )
     */
    public function apiAuthorize()
    {
        //角色ID
        $roleId = $this->request->param("id", 0, 'intval');
        if (empty($roleId)) {
            $this->error("参数错误！");
        }

        $privilegeData = AuthAccessModel::where("role_id", $roleId)->column('rule_name', 'rule_name');//获取权限表数据
        $adminApis     = AdminApiModel::select();
        $newAdminApis  = [];
        foreach ($adminApis as $adminApi) {
            if (isset($privilegeData[strtolower('admin_api:' . $adminApi['url'])])) {
                $adminApi['checked'] = 1;
            } else {
                $adminApi['checked'] = 0;
            }
            $newAdminApis[]        = $adminApi;
        }

        $this->success('success', ['list' => $newAdminApis, 'total' => $adminApis->count()]);
    }

    /**
     * 提交角色后台API授权信息
     * @throws \think\exception\DbException
     * @OA\Put(
     *     tags={"admin"},
     *     path="/admin/roles/{id}/api/authorize",
     *     summary="提交角色后台API授权信息",
     *     description="提交角色后台API授权信息",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="角色id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "授权成功","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "not found!","data":""})
     *     ),
     * )
     */
    public function apiAuthorizePut()
    {
        if ($this->request->isPut()) {
            $roleId = $this->request->param("id", 0, 'intval');
            if (!$roleId) {
                $this->error("需要授权的角色不存在！");
            } else {
                $role = RoleModel::find($roleId);
                if (empty($role)) {
                    $this->error("需要授权的角色不存在！");
                }
            }

            $adminApiIds = $this->request->param('ids/a');
            if (is_array($adminApiIds) && count($adminApiIds) > 0) {
                AuthAccessModel::where(["role_id" => $roleId, 'type' => 'admin_api'])->delete();
                foreach ($adminApiIds as $adminApiId) {
                    $adminApi = AdminApiModel::where("id", $adminApiId)->field('url')->find();
                    if ($adminApi) {
                        $name = strtolower("admin_api:{$adminApi['url']}");
                        AuthAccessModel::insert(["role_id" => $roleId, "rule_name" => $name, 'type' => 'admin_api']);
                    }
                }

                $this->success("授权成功！");
            } else {
                //当没有数据时，清除当前角色授权
                AuthAccessModel::where("role_id", $roleId)->where('type', 'admin_api')->delete();
                $this->error("没有接收到数据，执行清除授权成功！");
            }
        }
    }

    /**
     * 检查指定菜单是否有权限
     * @param array $menu menu表中数组
     * @param       $privData
     * @return bool
     */
    private function _isChecked($menu, $privData)
    {
        $app        = $menu['app'];
        $controller = $menu['controller'];
        $action     = $menu['action'];
        $name       = strtolower("$app/$controller/$action");
        if ($privData) {
            if (in_array($name, $privData)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

}

