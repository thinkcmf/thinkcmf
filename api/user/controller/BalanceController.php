<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------
namespace api\user\controller;

use api\user\model\UserBalanceLogModel;
use api\user\model\UserModel;
use cmf\controller\RestUserBaseController;


class BalanceController extends RestUserBaseController
{
    /**
     * 余额变更
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function logs()
    {
        $userId = $this->getUserId();

        $balanceModel = new UserBalanceLogModel();
        $result       = $balanceModel->where(['user_id' => $userId])->order('create_time desc')->paginate();

        $this->success('请求成功', ['list' => $result->items()]);
    }

    /**
     * 转账
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function transfer()
    {
        $userId   = $this->getUserId();
        $toUserId = $this->request->param('to_user_id', 0, 'intval');
        $amount   = $this->request->param('amount', 0, 'floatval');
        $remark   = $this->request->param('remark');

        $balanceModel = new UserBalanceLogModel();

        $userModel = new UserModel();

        $findToUser = $userModel->where('id', $toUserId)->find();

        if (empty($findToUser)) {
            $this->error('收款人不存在！');
        }

        $userModel->startTrans();
        $error = 0;
        try {
            $userBalance = $userModel->where('id', $userId)->lock(true)->value('balance');

            if ($userBalance > $amount) {
                $userModel->where('id', $userId)->setDec('balance', $amount);
                $balanceModel->insert([
                    'user_id'     => $userId,
                    'to_user_id'  => $toUserId,
                    'create_time' => time(),
                    'amount'      => 0 - $amount,
                    'description' => '转账',
                    'remark'      => $remark
                ]);

                $userModel->where('id', $toUserId)->setInc('balance', $amount);

                $balanceModel->insert([
                    'user_id'     => $toUserId,
                    'to_user_id'  => $userId,
                    'create_time' => time(),
                    'amount'      => $amount,
                    'description' => '收款',
                    'remark'      => $remark
                ]);

            } else {
                $error = 1;
            }

            $userModel->commit();

        } catch (\Exception $e) {
            $userModel->rollback();

            $this->error('操作失败！');
        }

        if ($error > 0) {
            switch ($error) {
                case 1:
                    $this->error('余额不足');
                    break;
            }
        } else {
            $this->success('转账成功！');
        }
    }


}