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

use cmf\controller\AdminBaseController;
use app\admin\model\LinkModel;

class ApiController extends AdminBaseController
{

    /**
     * 后台API导入
     * @adminMenu(
     *     'name'   => '后台API导入',
     *     'parent' => 'admin/Setting/default',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 50,
     *     'icon'   => '',
     *     'remark' => '后台API导入',
     *     'param'  => ''
     * )
     * @return mixed
     */
    public function import()
    {
        $content = hook_one('admin_api_import_view');

        if (!empty($content)) {
            return $content;
        }

        return $this->fetch();
    }

}
