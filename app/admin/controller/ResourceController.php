<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use think\Db;
use cmf\controller\AdminBaseController;

class ResourceController extends AdminBaseController
{
    public function index()
    {
        $result = Db::name('asset')->select();
        $this->assign('result', $result);
        $this->assign('status', ['不可用', '可用']);
        return $this->fetch();
    }

    public function delete()
    {
        $id   = $this->request->param('id');
        $file_filePath = Db::name('asset')->where('id', $id)->value('file_path');
        $file = 'upload/'.$file_filePath;
       // var_dump(file_exists('upload/'.$file));exit;
        if (file_exists($file)) {
//            print_r(1111);exit;
            $res = unlink($file);
            if ($res) {
                Db::name('asset')->where('id', $id)->delete();
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        }
    }


    public function cancelBan()
    {
        $id     = $this->request->param('id');
        $result = Db::name('asset')->where('id', $id)->update(['status' => 1]);
        if ($result) {
            $this->success('隐藏成功');
        } else {
            $this->error('隐藏失败');
        }
    }

    public function ban()
    {
        $id     = $this->request->param('id');
        $result = Db::name('asset')->where('id', $id)->update(['status' => 0]);
        if ($result) {
            $this->success('隐藏成功');
        } else {
            $this->error('隐藏失败');
        }
    }
}