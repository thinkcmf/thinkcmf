<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------

namespace api\user\controller;

use app\user\model\UserModel;
use cmf\controller\RestAdminBaseController;
use think\db\Query;

class AdminUserController extends RestAdminBaseController
{

    /**
     * 本站用户列表
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"user"},
     *     path="/admin/user/users",
     *     summary="本站用户列表",
     *     description="本站用户列表",
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="用户ID",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="keyword",
     *         in="query",
     *         description="关键字,可以搜索用户名，昵称，手机，邮箱",
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
     *                  {
     *                      "id": 2,"user_type": 1,"sex": 0,
     *                      "birthday": 0,"last_login_time": 1691213022,"score": 0,"coin": 0,"balance": "0.00",
     *                      "create_time": 1691213022,"user_status": 0,"user_login": "ddd",
     *                      "user_nickname": "","user_email": "sss@11.com","user_url": "","avatar": "",
     *                      "signature": "","last_login_ip": "","user_activation_key": "","mobile": "",
     *                      "more": null
     *                  }
     *              },
     *              "total":20
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
        $list = UserModel::where(function (Query $query) {
            $data = $this->request->param();
            if (!empty($data['user_id'])) {
                $query->where('id', intval($data['user_id']));
            }

            if (!empty($data['keyword'])) {
                $keyword = $data['keyword'];
                $query->where('user_login|user_nickname|user_email|mobile', 'like', "%$keyword%");
            }

        })->order("id DESC")
            ->paginate(10);

        if (!$list->isEmpty()) {
            $list->hidden(['user_pass']);
        }

        $this->success('success', ['list' => $list->items(), 'total' => $list->total()]);
    }

    /**
     * 设置用户状态
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"user"},
     *     path="/admin/user/users/{id}/status/{status}",
     *     summary="设置用户状态",
     *     description="设置用户状态",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="用户id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="path",
     *         description="用户状态,0:禁用;1:正常",
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
        if ($id) {
            $status = empty($status) ? 0 : 1;
            $result = UserModel::where(["id" => $id, "user_type" => 2])->update(['user_status' => $status]);
            if ($result) {
                $this->success("操作成功！",);
            } else {
                $this->error('操作失败,会员不存在,或者是管理员！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

}
