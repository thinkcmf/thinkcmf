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

use app\user\model\ThirdPartyUserModel;
use cmf\controller\RestAdminBaseController;
use OpenApi\Annotations as OA;
use think\db\Query;

class AdminOauthController extends RestAdminBaseController
{
    /**
     * 第三方用户列表
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"user"},
     *     path="/admin/user/oauth/users",
     *     summary="第三方用户列表",
     *     description="第三方用户列表",
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="本站用户ID",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="keyword",
     *         in="query",
     *         description="关键字,可以搜索昵称",
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
     *                      "id": 1,"user_id": 1,"last_login_time": 0,"expire_time": 0,"create_time": 0,"login_times": 0,"status": 1,
     *                       "nickname": "猫二","third_party": "","app_id": "","last_login_ip": "","access_token": "",
     *                      "openid": "","union_id": "","more": null,
     *                      "user": {  "id": 1,  "user_type": 1,  "sex": 0,  "birthday": 0,  "last_login_time": 1693579520,  "score": 1,  "coin": 1,  "balance": "0.00",  "create_time": 1684378993,  "user_status": 1,  "user_login": "admin",  "user_nickname": "admin",  "user_email": "sales@naturesci.cn",  "user_url": "",  "avatar": "",  "signature": "",  "last_login_ip": "172.21.0.1",  "user_activation_key": "",  "mobile": "",  "more": null}
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
        $list = ThirdPartyUserModel::where(function (Query $query) {
            $data = $this->request->param();
            if (!empty($data['user_id'])) {
                $query->where('user_id', intval($data['user_id']));
            }

            if (!empty($data['keyword'])) {
                $keyword = $data['keyword'];
                $query->where('nickname', 'like', "%$keyword%");
            }
        })
            ->order("create_time DESC")
            ->paginate(10);

        if (!$list->isEmpty()) {
            $list->load(['user']);
            $list->visible(['user.user_type','user.sex','user.user_login','user.user_nickname','user.avatar']);
        }

        $this->success('success', ['list' => $list->items(), 'total' => $list->total()]);
    }

    /**
     * 删除第三方用户绑定
     * @throws \think\exception\DbException
     * @OA\Delete(
     *     tags={"user"},
     *     path="/admin/user/oauth/users/{id}",
     *     summary="删除第三方用户绑定",
     *     description="删除第三方用户绑定",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="第三方用户表id",
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
     *          @OA\JsonContent(example={"code": 0,"msg": "数据传入失败！","data":""})
     *     ),
     * )
     */
    public function delete()
    {
        if ($this->request->isDelete()) {
            $id = $this->request->param('id', 0, 'intval');
            if (empty($id)) {
                $this->error(lang('illegal data'));
            }

            ThirdPartyUserModel::where("id", $id)->delete();
            $this->success(lang('DELETE_SUCCESS'));
        }
    }


}
