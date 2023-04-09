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
namespace app\admin\controller;

use app\admin\model\AdminApiModel;
use app\admin\model\AuthAccessModel;
use app\admin\model\RoleModel;
use app\admin\model\RoleUserModel;
use cmf\controller\AdminBaseController;
use think\facade\Cache;
use tree\Tree;
use app\admin\model\AdminMenuModel;

class RbacController extends AdminBaseController
{

    /**
     * 角色管理列表
     * @adminMenu(
     *     'name'   => '角色管理',
     *     'parent' => 'user/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '角色管理',
     *     'param'  => ''
     * )
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $content = hook_one('admin_rbac_index_view');

        if (!empty($content)) {
            return $content;
        }

        $data = RoleModel::order(["list_order" => "ASC", "id" => "DESC"])->select();
        $this->assign("roles", $data);
        return $this->fetch();
    }

    /**
     * 添加角色
     * @adminMenu(
     *     'name'   => '添加角色',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加角色',
     *     'param'  => ''
     * )
     * @return mixed
     */
    public function roleAdd()
    {
        $content = hook_one('admin_rbac_role_add_view');

        if (!empty($content)) {
            return $content;
        }

        return $this->fetch();
    }

    /**
     * 添加角色提交
     * @adminMenu(
     *     'name'   => '添加角色提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加角色提交',
     *     'param'  => ''
     * )
     */
    public function roleAddPost()
    {
        if ($this->request->isPost()) {
            $data   = $this->request->param();
            $result = $this->validate($data, 'role');
            if ($result !== true) {
                // 验证失败 输出错误信息
                $this->error($result);
            } else {
                $result = RoleModel::insert($data);
                if ($result) {
                    $this->success(lang('ADD_SUCCESS'), url("rbac/index"));
                } else {
                    $this->error(lang('ADD_FAILED'));
                }

            }
        }
    }

    /**
     * 编辑角色
     * @adminMenu(
     *     'name'   => '编辑角色',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑角色',
     *     'param'  => ''
     * )
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function roleEdit()
    {
        $content = hook_one('admin_rbac_role_edit_view');

        if (!empty($content)) {
            return $content;
        }

        $id = $this->request->param("id", 0, 'intval');
        if ($id == 1) {
            $this->error("超级管理员角色不能被修改！");
        }
        $data = RoleModel::where("id", $id)->find();
        if (!$data) {
            $this->error("该角色不存在！");
        }
        $this->assign("data", $data);
        return $this->fetch();
    }

    /**
     * 编辑角色提交
     * @adminMenu(
     *     'name'   => '编辑角色提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑角色提交',
     *     'param'  => ''
     * )
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function roleEditPost()
    {
        $id = $this->request->param("id", 0, 'intval');
        if ($id == 1) {
            $this->error("超级管理员角色不能被修改！");
        }
        if ($this->request->isPost()) {
            $data   = $this->request->param();
            $result = $this->validate($data, 'role');
            if ($result !== true) {
                // 验证失败 输出错误信息
                $this->error($result);

            } else {
                if (RoleModel::update($data) !== false) {
                    $this->success(lang('EDIT_SUCCESS'), url('rbac/index'));
                } else {
                    $this->error(lang('EDIT_FAILED'));
                }
            }
        }
    }

    /**
     * 删除角色
     * @adminMenu(
     *     'name'   => '删除角色',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除角色',
     *     'param'  => ''
     * )
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function roleDelete()
    {
        if ($this->request->isPost()) {
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
                    $this->success(lang('DELETE_SUCCESS'), url('rbac/index'));
                } else {
                    $this->error(lang('DELETE_FAILED'));
                }
            }
        }
    }

    /**
     * 设置角色权限
     * @adminMenu(
     *     'name'   => '设置角色权限',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '设置角色权限',
     *     'param'  => ''
     * )
     * @return mixed
     */
    public function authorize()
    {
        $content = hook_one('admin_rbac_authorize_view');

        if (!empty($content)) {
            return $content;
        }

        $adminMenuModel = new AdminMenuModel();
        //角色ID
        $roleId = $this->request->param("id", 0, 'intval');
        if (empty($roleId)) {
            $this->error("参数错误！");
        }

        $tree       = new Tree();
        $tree->icon = ['│ ', '├─ ', '└─ '];
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';

        $result = $adminMenuModel->menuCache();

        $newMenus      = [];
        $privilegeData = AuthAccessModel::where("role_id", $roleId)->column("rule_name");//获取权限表数据

        foreach ($result as $m) {
            $newMenus[$m['id']] = $m;
        }

        foreach ($result as $n => $t) {
            $result[$n]['checked']      = ($this->_isChecked($t, $privilegeData)) ? ' checked' : '';
            $result[$n]['level']        = $this->_getLevel($t['id'], $newMenus);
            $result[$n]['style']        = empty($t['parent_id']) ? '' : 'display:none;';
            $result[$n]['parentIdNode'] = ($t['parent_id']) ? ' class="child-of-node-' . $t['parent_id'] . '"' : '';
        }

        $str = "<tr id='node-\$id'\$parentIdNode  style='\$style'>
                   <td style='padding-left:30px;'>\$spacer<input type='checkbox' name='menuId[]' value='\$id' level='\$level' \$checked onclick='javascript:checknode(this);'> \$name \$app/\$controller/\$action</td>
    			</tr>";
        $tree->init($result);

        $category = $tree->getTree(0, $str);

        $this->assign("category", $category);
        $this->assign("roleId", $roleId);
        $this->assign("role_id", $roleId);
        return $this->fetch();
    }

    /**
     * 角色授权提交
     * @adminMenu(
     *     'name'   => '角色授权提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '角色授权提交',
     *     'param'  => ''
     * )
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function authorizePost()
    {
        if ($this->request->isPost()) {
            $roleId = $this->request->param("roleId", 0, 'intval');
            if (!$roleId) {
                $this->error("需要授权的角色不存在！");
            }
            $menuIds = $this->request->param('menuId/a');
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
     * 设置角色后台API权限
     * @adminMenu(
     *     'name'   => '设置角色后台API权限',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '设置角色后台API权限',
     *     'param'  => ''
     * )
     * @return mixed
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
        $tagsAdminApis = [];

        foreach ($adminApis as $adminApi) {
            if (isset($privilegeData[strtolower('admin_api:' . $adminApi['url'])])) {
                $adminApi['_checked'] = 1;
            } else {
                $adminApi['_checked'] = 0;
            }
            $tags = explode(',', $adminApi['tags']);
            foreach ($tags as $tag) {
                if (empty($tagsAdminApis[$tag])) {
                    $tagsAdminApis[$tag] = [];
                }
                $tagsAdminApis[$tag][] = $adminApi;
            }
        }

        $this->assign("admin_apis", $adminApis);
        $this->assign("tags_admin_apis", $tagsAdminApis);
        $this->assign("role_id", $roleId);
        return $this->fetch('api_authorize');
    }

    /**
     * 设置角色后台API权限提交
     * @adminMenu(
     *     'name'   => '设置角色后台API权限提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '设置角色后台API权限提交',
     *     'param'  => ''
     * )
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function apiAuthorizePost()
    {
        if ($this->request->isPost()) {
            $roleId = $this->request->param("role_id", 0, 'intval');
            if (!$roleId) {
                $this->error("需要授权的角色不存在！");
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
                AuthAccessModel::where("role_id", $roleId)->where('type','admin_api')->delete();
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

    /**
     * 获取菜单深度
     * @param       $id
     * @param array $array
     * @param int   $i
     * @return int
     */
    protected function _getLevel($id, $array = [], $i = 0)
    {
        if ($array[$id]['parent_id'] == 0 || empty($array[$array[$id]['parent_id']]) || $array[$id]['parent_id'] == $id) {
            return $i;
        } else {
            $i++;
            return $this->_getLevel($array[$id]['parent_id'], $array, $i);
        }
    }

    //角色成员管理
    public function member()
    {
        //TODO 添加角色成员管理

    }

}

