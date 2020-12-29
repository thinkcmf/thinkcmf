<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace plugins\portal\controller; //Demo插件英文名，改成你的插件英文就行了

use cmf\controller\PluginAdminBaseController;
use think\Db;

class AdminIndexController extends PluginAdminBaseController
{

    /**
     * 门户设置
     * @adminMenu(
     *     'name'   => '门户设置',
     *     'parent' => 'portal/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '门户设置',
     *     'param'  => ''
     * )
     */
    public function setting()
    {
        $data = Db::name('role')->order(["list_order" => "ASC", "id" => "DESC"])->select();
        $this->assign("roles", $data);
        return $this->fetch();

        return $this->fetch();
    }

}
