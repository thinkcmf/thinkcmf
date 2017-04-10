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

class SlideItemController extends AdminBaseController
{
    /**
     * 幻灯片页面列表
     */
    public function index()
    {
        $id      = $this->request->param('slide_id');
        $slideId = !empty($id) ? $id : 1;
        $result  = Db::name('slideItem')->where(array('slide_id' => $slideId))->select()->toArray();
        foreach ($result as $key => $value) {
            $result[$key]['picture'] = preg_replace('/\\\\/', '/', $value['picture']);
        }

        $status = [
            '隐藏',
            '开启'

        ];
        $this->assign('result', $result);
        $this->assign('status', $status);
        return $this->fetch();
    }

    /**
     * 幻灯片页面添加
     */
    public function add()
    {
        $categories = Db::name('slide')->where('status', 1)->select();
        $this->assign('categories', $categories);
        return $this->fetch();
    }

    /**
     * 幻灯片页面添加提交保存
     */
    public function addPost()
    {
        $data = $this->request->param();
        Db::name('slideItem')->insert($data['post']);
        $this->success("添加成功！", url("slideItem/index"));
    }

    /**
     * 幻灯片页面编辑
     */
    public function edit()
    {
        $id         = $this->request->param('id');
        $result     = Db::name('slideItem')->where(array('id' => $id))->find();
        $categories = Db::name('slide')->where('status', 1)->select();
        $this->assign('categories', $categories);
        $this->assign('result', $result);
        return $this->fetch();
    }

    /**
     * 幻灯片页面编辑提交保存
     */
    public function editPost()
    {
        $data = $this->request->param();
        if ($data['more']['thumb']) {
            $data['post']['picture'] = $data['more']['thumb'];
        }
        $result = Db::name('slideItem')->update($data['post']);
        if ($result) {
            $this->success("修改成功！", url("SlideItem/index"));
        } else {
            $this->error('修改失败！');
        }

    }

    /**
     * 幻灯片页面删除
     */
    public function delete()
    {
        $id     = $this->request->param('id', 0, 'intval');
        $result = Db::name('slideItem')->delete($id);
        if ($result) {
            $this->success("删除成功！", url("SlideItem/index"));
        } else {
            $this->error('删除失败！');
        }

    }

    // 幻灯片隐藏
    public function ban()
    {
        $id = $this->request->param('id', 0, 'intval');
        if ($id) {
            $rst = Db::name('slideItem')->where(array('id' => $id))->update(array('status' => 0));
            if ($rst) {
                $this->success("幻灯片隐藏成功！");
            } else {
                $this->error('幻灯片隐藏失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    // 幻灯片启用
    public function cancelBan()
    {
        $id = $this->request->param('id', 0, 'intval');
        if ($id) {
            $result = Db::name('slideItem')->where(array('id' => $id))->update(array('status' => 1));
            if ($result) {
                $this->success("幻灯片启用成功！");
            } else {
                $this->error('幻灯片启用失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }
}