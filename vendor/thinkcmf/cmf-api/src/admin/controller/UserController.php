<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace api\admin\controller;

use app\admin\logic\UserLogic;
use app\admin\model\RecycleBinModel;
use app\admin\model\RoleModel;
use app\admin\model\RoleUserModel;
use app\admin\model\UserModel;
use cmf\controller\RestAdminBaseController;
use OpenApi\Annotations as OA;
use think\db\Query;

class UserController extends RestAdminBaseController
{
    /**
     * 管理员列表
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/users",
     *     summary="管理员列表",
     *     description="管理员列表",
     *     @OA\Parameter(
     *         name="user_login",
     *         in="query",
     *         description="用户名",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="user_email",
     *         in="query",
     *         description="邮箱",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "list":{
     *                  {"id": 2,"user_type": 1,"sex": 0,"birthday": 0,
     *                      "last_login_time": 1691213022,"score": 0,"coin": 0,
     *                      "balance": "0.00","create_time": 1691213022,
     *                      "user_status": 1,"user_login": "ddd",
     *                      "user_pass": "###1b05af0646905424b39239236fbd043c",
     *                      "user_nickname": "","user_email": "sss@11.com",
     *                      "user_url": "","avatar": "",
     *                      "signature": "","last_login_ip": "",
     *                      "user_activation_key": "","mobile": "",
     *                  "more": null}
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
        $users = UserModel::where('user_type', 1)
            ->where(function (Query $query) {
                $userLogin = trim($this->request->param('user_login', ''));
                $userEmail = trim($this->request->param('user_email', ''));
                if ($userLogin) {
                    $query->where('user_login', 'like', "%$userLogin%");
                }

                if ($userEmail) {
                    $query->where('user_email', 'like', "%$userEmail%");
                }
            })
            ->order("id DESC")
            ->paginate(10);

        if (!$users->isEmpty()) {
            $users->hidden(['user_pass']);
        }

        $this->success('success', ['list' => $users->items(), 'total' => $users->total()]);
    }

    /**
     * 添加管理员
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/users",
     *     summary="添加管理员",
     *     description="添加管理员",
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/AdminUserSaveRequestForm")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AdminUserSaveRequest")
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "item":{"id": 1,"status": 1,"delete_time": 0,"name": "又菜又爱玩","remark": ""}
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
            $roleIds = $this->request->param('role_ids/a');
            if (!empty($roleIds) && is_array($roleIds)) {
                $data   = $this->request->param();
                $result = $this->validate($data, 'User.add');
                if ($result !== true) {
                    $this->error($result);
                } else {
                    $data['user_pass']       = cmf_password($data['user_pass']);
                    $data['create_time']     = time();
                    $data['last_login_time'] = $data['create_time'];
                    $user                    = UserModel::create($data);
                    if (!empty($user['id'])) {
                        foreach ($roleIds as $roleId) {
                            if ($this->getUserId() != 1 && $roleId == 1) {
                                $this->error("为了网站的安全，非网站创建者不可创建超级管理员！");
                            }
                            RoleUserModel::insert(["role_id" => $roleId, "user_id" => $user['id']]);
                        }
                        $this->success(lang('ADD_SUCCESS'), ['item' => $user]);
                    } else {
                        $this->error(lang('ADD_FAILED'));
                    }
                }
            } else {
                $this->error("请为此用户指定角色！");
            }

        }
    }

    /**
     * 获取管理员信息
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/users/{id}",
     *     summary="获取管理员信息",
     *     description="获取管理员信息",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="管理员id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "item":{"id": 1,"status": 1,"delete_time": 0,"name": "又菜又爱玩","remark": ""}
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
        $id      = $this->request->param('id', 0, 'intval');
        $roles   = RoleModel::where('status', 1)->order("id DESC")->select();
        $roleIds = RoleUserModel::where("user_id", $id)->column("role_id");

        $user = UserModel::where("id", $id)->find()->toArray();
        $this->success('success', ['item' => $user, 'role_ids' => $roleIds, 'roles' => $roles]);
    }

    /**
     * 编辑管理员
     * @throws \think\exception\DbException
     * @OA\Put(
     *     tags={"admin"},
     *     path="/admin/users/{id}",
     *     summary="编辑管理员",
     *     description="编辑管理员",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="管理员id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/AdminUserSaveRequestForm")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AdminUserSaveRequest")
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
        $roleIds = $this->request->param('role_ids/a');
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
                $currentUserId = $this->getUserId();
                $userId        = $this->request->param('id', 0, 'intval');
                $result        = UserModel::strict(false)->where('id', $userId)->save($data);
                if ($result !== false) {
                    RoleUserModel::where("user_id", $userId)->delete();
                    foreach ($roleIds as $roleId) {
                        if ($currentUserId != 1 && $roleId == 1) {
                            $this->error("为了网站的安全，非网站创建者不可创建超级管理员！");
                        }
                        RoleUserModel::insert(["role_id" => $roleId, "user_id" => $userId]);
                    }
                    $this->success(lang('EDIT_SUCCESS'));
                } else {
                    $this->error(lang('EDIT_FAILED'));
                }
            }
        } else {
            $this->error("请为此用户指定角色！");
        }
    }

    /**
     * 删除管理员
     * @throws \think\exception\DbException
     * @OA\Delete(
     *     tags={"admin"},
     *     path="/admin/users/{id}",
     *     summary="删除管理员",
     *     description="删除管理员",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="管理员id",
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
        $id = $this->request->param('id', 0, 'intval');
        if (!UserLogic::isCreator($this->getUserId())) {
            $this->error('为了网站的安全，非网站创建者不可删除');
        }
        if ($id == 1) {
            $this->error("最高管理员不能删除！");
        }

        if (UserModel::destroy($id) !== false) {
            RoleUserModel::where('user_id', $id)->delete();
            $this->success(lang('DELETE_SUCCESS'));
        } else {
            $this->error(lang('DELETE_FAILED'));
        }
    }

    /**
     * 设置管理员启用状态
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/users/{id}/status/{status}",
     *     summary="设置管理员启用状态",
     *     description="设置管理员启用状态",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="管理员id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="path",
     *         description="状态,0:禁用;1:启用",
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
     *          @OA\JsonContent(example={"code": 0,"msg": "数据传入失败！","data":""})
     *     ),
     * )
     */
    public function status()
    {
        $id     = $this->request->param('id', 0, 'intval');
        $status = $this->request->param('status', 0, 'intval');
        if (!empty($id)) {
            if (!UserLogic::isCreator($this->getUserId())) {
                $this->error('为了网站的安全，非网站创建者不可拉黑');
            }
            $status = empty($status) ? 0 : 1;
            UserModel::where(['id' => $id])->update(['user_status' => $status]);
            $this->success('操作成功！');
        } else {
            $this->error('数据传入失败！');
        }
    }

}
