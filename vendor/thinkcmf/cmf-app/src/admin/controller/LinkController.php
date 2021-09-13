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

use app\admin\model\LinkModel;
use cmf\controller\AdminBaseController;

class LinkController extends AdminBaseController
{
    /**
     * 友情链接管理
     * @adminMenu(
     *     'name'   => '友情链接',
     *     'parent' => 'admin/Setting/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 50,
     *     'icon'   => '',
     *     'remark' => '友情链接管理',
     *     'param'  => ''
     * )
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $content = hook_one('admin_link_index_view');

        if (!empty($content)) {
            return $content;
        }

        $linkModel = new LinkModel();
        $links     = $linkModel->select();
        $this->assign('links', $links);

        return $this->fetch();
    }

    /**
     * 添加友情链接
     * @adminMenu(
     *     'name'   => '添加友情链接',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加友情链接',
     *     'param'  => ''
     * )
     */
    public function add()
    {
        $targets = [
            "_blank" => lang('ADMIN_OPEN_MODE_1'),
            "_self"  => lang('ADMIN_OPEN_MODE_2')
        ];
        $this->assign('targets', $targets);
        return $this->fetch();
    }

    /**
     * 添加友情链接提交保存
     * @adminMenu(
     *     'name'   => '添加友情链接提交保存',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加友情链接提交保存',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        if ($this->request->isPost()) {
            $data      = $this->request->param();
            $linkModel = new LinkModel();
            $result    = $this->validate($data, 'Link');
            if ($result !== true) {
                $this->error($result);
            }
            $linkModel->save($data);

            $this->success(lang('ADD_SUCCESS'), url("Link/index"));
        }
    }

    /**
     * 编辑友情链接
     * @adminMenu(
     *     'name'   => '编辑友情链接',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑友情链接',
     *     'param'  => ''
     * )
     * @return mixed
     * @throws \think\Exception\DbException
     */
    public function edit()
    {
        $targets   = ["_blank" => lang('ADMIN_OPEN_MODE_1'), "_self" => lang('ADMIN_OPEN_MODE_2')];
        $id        = $this->request->param('id', 0, 'intval');
        $linkModel = new LinkModel();
        $link      = $linkModel->find($id);
        $this->assign('targets', $targets);
        $this->assign('link', $link);
        return $this->fetch();
    }

    /**
     * 编辑友情链接提交保存
     * @adminMenu(
     *     'name'   => '编辑友情链接提交保存',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑友情链接提交保存',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {
        if ($this->request->isPost()) {
            $data   = $this->request->param();
            $result = $this->validate($data, 'Link');
            if ($result !== true) {
                $this->error($result);
            }
            $linkModel = LinkModel::find($data['id']);
            $linkModel->save($data);

            $this->success(lang('EDIT_SUCCESS'), url("Link/index"));
        }
    }

    /**
     * 删除友情链接
     * @adminMenu(
     *     'name'   => '删除友情链接',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除友情链接',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        if ($this->request->isPost()) {
            $id = $this->request->param('id', 0, 'intval');
            LinkModel::destroy($id);
            $this->success(lang('DELETE_SUCCESS'), url("Link/index"));
        }
    }

    /**
     * 友情链接排序
     * @adminMenu(
     *     'name'   => '友情链接排序',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '友情链接排序',
     *     'param'  => ''
     * )
     */
    public function listOrder()
    {
        $linkModel = new  LinkModel();
        parent::listOrders($linkModel);
        $this->success(lang('SORT_SUCCESS'));
    }

    /**
     * 友情链接显示隐藏
     * @adminMenu(
     *     'name'   => '友情链接显示隐藏',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '友情链接显示隐藏',
     *     'param'  => ''
     * )
     */
    public function toggle()
    {
        if ($this->request->isPost()) {
            $data      = $this->request->param();
            $linkModel = new LinkModel();

            if (isset($data['ids']) && !empty($data["display"])) {
                $ids = $this->request->param('ids/a');
                $linkModel->where('id', 'in', $ids)->update(['status' => 1]);
                $this->success(lang('UPDATE_SUCCESS'));
            }

            if (isset($data['ids']) && !empty($data["hide"])) {
                $ids = $this->request->param('ids/a');
                $linkModel->where('id', 'in', $ids)->update(['status' => 0]);
                $this->success(lang('UPDATE_SUCCESS'));
            }
        }
    }

}
