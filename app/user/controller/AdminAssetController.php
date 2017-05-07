<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\user\controller;

use think\Db;
use cmf\controller\AdminBaseController;

class AdminAssetController extends AdminBaseController
{
    /**
     * 资源管理列表
     * @adminMenu(
     *     'name'   => '资源管理',
     *     'parent' => '',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => 'file',
     *     'remark' => '资源管理列表',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $result = Db::name('asset')->select();
        $user= Db::name('user')->column('user_login','id');
        $this->assign('user', $user);
        $this->assign('result', $result);
        $this->assign('status', ['不可用', '可用']);
        return $this->fetch();
    }

    /**
     * 删除文件
     * @adminMenu(
     *     'name'   => '删除文件',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除文件',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $id   = $this->request->param('id');
        $file_filePath = Db::name('asset')->where('id', $id)->value('file_path');
        $file = 'upload/' . $file_filePath;
        if (file_exists($file)) {
            $res = unlink($file);
            if ($res) {
                Db::name('asset')->where('id', $id)->delete();
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        }
    }

}