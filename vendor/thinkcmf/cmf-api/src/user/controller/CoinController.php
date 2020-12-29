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
namespace app\user\controller;

use api\user\model\UserScoreLogModel;
use cmf\controller\RestUserBaseController;

class CoinController extends RestUserBaseController
{
    /**
     * 查询金币日志
     * @throws \think\exception\DbException
     */
    public function logs()
    {
        $userId            = $this->getUserId();
        $userScoreLogModel = new UserScoreLogModel();

        $logs = $userScoreLogModel->where('user_id', $userId)
            ->where('coin', '<>', 0)
            ->order('create_time DESC')->paginate();

        $this->success('请求成功', ['list' => $logs->items()]);
    }

}
