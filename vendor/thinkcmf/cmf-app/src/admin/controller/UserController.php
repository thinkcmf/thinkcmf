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

use app\admin\model\RoleModel;
use app\admin\model\RoleUserModel;
use app\admin\model\UserModel;
use cmf\controller\AdminBaseController;
use think\db\Query;

/**
 * Class UserController
 * @package app\admin\controller
 * @adminMenuRoot(
 *     'name'   => '管理组',
 *     'action' => 'default',
 *     'parent' => 'user/AdminIndex/default',
 *     'display'=> true,
 *     'order'  => 10000,
 *     'icon'   => '',
 *     'remark' => '管理组'
 * )
 */
class UserController extends AdminBaseController
{

    /**
     * 管理员列表
     * @adminMenu(
     *     'name'   => '管理员',
     *     'parent' => 'default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员管理',
     *     'param'  => ''
     * )
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $content = hook_one('admin_user_index_view');

        if (!empty($content)) {
            return $content;
        }

        /**搜索条件**/
        $userLogin = $this->request->param('user_login');
        $userEmail = trim($this->request->param('user_email'));

        $users = UserModel::where('user_type', 1)
            ->where(function (Query $query) use ($userLogin, $userEmail) {
                if ($userLogin) {
                    $query->where('user_login', 'like', "%$userLogin%");
                }

                if ($userEmail) {
                    $query->where('user_email', 'like', "%$userEmail%");
                }
            })
            ->order("id DESC")
            ->paginate(10);
        $users->appends(['user_login' => $userLogin, 'user_email' => $userEmail]);
        // 获取分页显示
        $page = $users->render();

        $rolesSrc = RoleModel::select();
        $roles    = [];
        foreach ($rolesSrc as $r) {
            $roleId           = $r['id'];
            $roles["$roleId"] = $r;
        }
        $this->assign("page", $page);
        $this->assign("roles", $roles);
        $this->assign("users", $users);
        return $this->fetch();
    }

    /**
     * 管理员添加
     * @adminMenu(
     *     'name'   => '管理员添加',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员添加',
     *     'param'  => ''
     * )
     */
    public function add()
    {
        $content = hook_one('admin_user_add_view');

        if (!empty($content)) {
            return $content;
        }

        $roles = RoleModel::where('status', 1)->order("id DESC")->select();
        $this->assign("roles", $roles);
        return $this->fetch();
    }

    /**
     * 管理员添加提交
     * @adminMenu(
     *     'name'   => '管理员添加提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员添加提交',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        if ($this->request->isPost()) {
            $roleIds = $this->request->param('role_id/a');
            if (!empty($roleIds) && is_array($roleIds)) {
                $data   = $this->request->param();
                $result = $this->validate($data, 'User');
                if ($result !== true) {
                    $this->error($result);
                } else {
                    $data['user_pass'] = cmf_password($data['user_pass']);
                    $userId            = UserModel::strict(false)->insertGetId($data);
                    if ($userId !== false) {
                        //$role_user_model=M("RoleUser");
                        foreach ($roleIds as $roleId) {
                            if (cmf_get_current_admin_id() != 1 && $roleId == 1) {
                                $this->error("为了网站的安全，非网站创建者不可创建超级管理员！");
                            }
                            RoleUserModel::insert(["role_id" => $roleId, "user_id" => $userId]);
                        }
                        $this->success("添加成功！", url("User/index"));
                    } else {
                        $this->error("添加失败！");
                    }
                }
            } else {
                $this->error("请为此用户指定角色！");
            }

        }
    }

    /**
     * 管理员编辑
     * @adminMenu(
     *     'name'   => '管理员编辑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员编辑',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        $content = hook_one('admin_user_edit_view');

        if (!empty($content)) {
            return $content;
        }

        $id    = $this->request->param('id', 0, 'intval');
        $roles = RoleModel::where('status', 1)->order("id DESC")->select();
        $this->assign("roles", $roles);
        $role_ids = RoleUserModel::where("user_id", $id)->column("role_id");
        $this->assign("role_ids", $role_ids);

        $user = UserModel::where("id", $id)->find()->toArray();
        $this->assign($user);
        return $this->fetch();
    }

    /**
     * 管理员编辑提交
     * @adminMenu(
     *     'name'   => '管理员编辑提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员编辑提交',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {
        if ($this->request->isPost()) {
            $roleIds = $this->request->param('role_id/a');
            if (!empty($roleIds) && is_array($roleIds)) {
                $data = $this->request->param();
                if (empty($data['user_pass'])) {
                    unset($data['user_pass']);
                } else {
                    $data['user_pass'] = cmf_password($data['user_pass']);
                }
                $result = $this->validate($data, 'User.edit');

                if ($result !== true) {
                    // 验证失败 输出错误信息
                    $this->error($result);
                } else {
                    $userId = $this->request->param('id', 0, 'intval');
                    $result = UserModel::strict(false)->where('id', $userId)->update($data);
                    if ($result !== false) {
                        RoleUserModel::where("user_id", $userId)->delete();
                        foreach ($roleIds as $roleId) {
                            if (cmf_get_current_admin_id() != 1 && $roleId == 1) {
                                $this->error("为了网站的安全，非网站创建者不可创建超级管理员！");
                            }
                            RoleUserModel::insert(["role_id" => $roleId, "user_id" => $userId]);
                        }
                        $this->success("保存成功！");
                    } else {
                        $this->error("保存失败！");
                    }
                }
            } else {
                $this->error("请为此用户指定角色！");
            }

        }
    }

    /**
     * 管理员个人信息修改
     * @adminMenu(
     *     'name'   => '个人信息',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员个人信息修改',
     *     'param'  => ''
     * )
     */
    public function userInfo()
    {
        $id   = cmf_get_current_admin_id();
        $user = UserModel::where("id", $id)->find()->toArray();
        $this->assign($user);
        return $this->fetch();
    }

    /**
     * 管理员个人信息修改提交
     * @adminMenu(
     *     'name'   => '管理员个人信息修改提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员个人信息修改提交',
     *     'param'  => ''
     * )
     */
    public function userInfoPost()
    {
        if ($this->request->isPost()) {

            $data             = $this->request->post();
            $data['birthday'] = strtotime($data['birthday']);
            $data['id']       = cmf_get_current_admin_id();
            $create_result    = UserModel::update($data);;
            if ($create_result !== false) {
                $this->success("保存成功！");
            } else {
                $this->error("保存失败！");
            }
        }
    }

    /**
     * 管理员删除
     * @adminMenu(
     *     'name'   => '管理员删除',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员删除',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        if ($this->request->isPost()) {
            $id = $this->request->param('id', 0, 'intval');
            if ($id == 1) {
                $this->error("最高管理员不能删除！");
            }

            if (UserModel::delete($id) !== false) {
                RoleUserModel::where("user_id", $id)->delete();
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }

    /**
     * 停用管理员
     * @adminMenu(
     *     'name'   => '停用管理员',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '停用管理员',
     *     'param'  => ''
     * )
     */
    public function ban()
    {
        if ($this->request->isPost()) {
            $id = $this->request->param('id', 0, 'intval');
            if (!empty($id)) {
                $result = UserModel::where(["id" => $id, "user_type" => 1])->update(['user_status' => '0']);
                if ($result !== false) {
                    $this->success("管理员停用成功！", url("User/index"));
                } else {
                    $this->error('管理员停用失败！');
                }
            } else {
                $this->error('数据传入失败！');
            }
        }
    }

    /**
     * 启用管理员
     * @adminMenu(
     *     'name'   => '启用管理员',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '启用管理员',
     *     'param'  => ''
     * )
     */
    public function cancelBan()
    {
        if ($this->request->isPost()) {
            $id = $this->request->param('id', 0, 'intval');
            if (!empty($id)) {
                $result = UserModel::where(["id" => $id, "user_type" => 1])->update(['user_status' => '1']);
                if ($result !== false) {
                    $this->success("管理员启用成功！", url("User/index"));
                } else {
                    $this->error('管理员启用失败！');
                }
            } else {
                $this->error('数据传入失败！');
            }
        }
    }
}
