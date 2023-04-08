<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace api\admin\controller;

use cmf\controller\RestAdminBaseController;
use cmf\controller\RestBaseController;
use think\facade\Db;
use think\facade\Validate;

class SettingController extends RestAdminBaseController
{
    // 管理员退出
    public function clearCache()
    {
        cmf_clear_cache();
        $this->success('清除成功！');
    }

}
