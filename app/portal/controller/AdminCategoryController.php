<?php

// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------

namespace app\portal\controller;

use app\admin\model\RouteModel;
use app\admin\model\ThemeModel;
use app\portal\model\PortalCategoryModel;
use cmf\controller\AdminBaseController;
use think\Db;

class AdminCategoryController extends AdminBaseController
{
    /**
     * 文章分类列表.
     *
     * @adminMenu(
     *     'name'   => '分类管理',
     *     'parent' => 'portal/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '文章分类列表',
     *     'param'  => ''
     * )
     *
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     *
     * @return mixed
     */
    public function index()
    {
        $content = hook_one('portal_admin_category_index_view');

        if (!empty($content)) {
            return $content;
        }

        $portalCategoryModel = new PortalCategoryModel();
        $keyword = $this->request->param('keyword');

        if (empty($keyword)) {
            $categoryTree = $portalCategoryModel->adminCategoryTableTree();
            $this->assign('category_tree', $categoryTree);
        } else {
            $categories = $portalCategoryModel->where('name', 'like', "%{$keyword}%")
                ->where('delete_time', 0)->select();
            $this->assign('categories', $categories);
        }

        $this->assign('keyword', $keyword);

        return $this->fetch();
    }

    /**
     * 添加文章分类.
     *
     * @adminMenu(
     *     'name'   => '添加文章分类',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加文章分类',
     *     'param'  => ''
     * )
     *
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     *
     * @return mixed
     */
    public function add()
    {
        $content = hook_one('portal_admin_category_add_view');

        if (!empty($content)) {
            return $content;
        }

        $parentId = $this->request->param('parent', 0, 'intval');
        $portalCategoryModel = new PortalCategoryModel();
        $categoriesTree = $portalCategoryModel->adminCategoryTree($parentId);

        $themeModel = new ThemeModel();
        $listThemeFiles = $themeModel->getActionThemeFiles('portal/List/index');
        $articleThemeFiles = $themeModel->getActionThemeFiles('portal/Article/index');

        $this->assign('list_theme_files', $listThemeFiles);
        $this->assign('article_theme_files', $articleThemeFiles);
        $this->assign('categories_tree', $categoriesTree);

        return $this->fetch();
    }

    /**
     * 添加文章分类提交.
     *
     * @adminMenu(
     *     'name'   => '添加文章分类提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加文章分类提交',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        $portalCategoryModel = new PortalCategoryModel();

        $data = $this->request->param();

        $result = $this->validate($data, 'PortalCategory');

        if ($result !== true) {
            $this->error($result);
        }

        $result = $portalCategoryModel->addCategory($data);

        if ($result === false) {
            $this->error('添加失败!');
        }

        $this->success('添加成功!', url('AdminCategory/index'));
    }

    /**
     * 编辑文章分类.
     *
     * @adminMenu(
     *     'name'   => '编辑文章分类',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑文章分类',
     *     'param'  => ''
     * )
     *
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     *
     * @return mixed
     */
    public function edit()
    {
        $content = hook_one('portal_admin_category_edit_view');

        if (!empty($content)) {
            return $content;
        }

        $id = $this->request->param('id', 0, 'intval');
        if ($id > 0) {
            $portalCategoryModel = new PortalCategoryModel();
            $category = $portalCategoryModel->get($id)->toArray();

            $categoriesTree = $portalCategoryModel->adminCategoryTree($category['parent_id'], $id);

            $themeModel = new ThemeModel();
            $listThemeFiles = $themeModel->getActionThemeFiles('portal/List/index');
            $articleThemeFiles = $themeModel->getActionThemeFiles('portal/Article/index');

            $routeModel = new RouteModel();
            $alias = $routeModel->getUrl('portal/List/index', ['id' => $id]);

            $category['alias'] = $alias;
            $this->assign($category);
            $this->assign('list_theme_files', $listThemeFiles);
            $this->assign('article_theme_files', $articleThemeFiles);
            $this->assign('categories_tree', $categoriesTree);

            return $this->fetch();
        } else {
            $this->error('操作错误!');
        }
    }

    /**
     * 编辑文章分类提交.
     *
     * @adminMenu(
     *     'name'   => '编辑文章分类提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑文章分类提交',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {
        $data = $this->request->param();

        $result = $this->validate($data, 'PortalCategory');

        if ($result !== true) {
            $this->error($result);
        }

        $portalCategoryModel = new PortalCategoryModel();

        $result = $portalCategoryModel->editCategory($data);

        if ($result === false) {
            $this->error('保存失败!');
        }

        $this->success('保存成功!');
    }

    /**
     * 文章分类选择对话框.
     *
     * @adminMenu(
     *     'name'   => '文章分类选择对话框',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '文章分类选择对话框',
     *     'param'  => ''
     * )
     *
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     *
     * @return mixed
     */
    public function select()
    {
        $ids = $this->request->param('ids');
        $selectedIds = explode(',', $ids);
        $portalCategoryModel = new PortalCategoryModel();

        $tpl = <<<'tpl'
<tr class='data-item-tr'>
    <td>
        <input type='checkbox' class='js-check' data-yid='js-check-y' data-xid='js-check-x' name='ids[]'
               value='$id' data-name='$name' $checked>
    </td>
    <td>$id</td>
    <td>$spacer <a href='$url' target='_blank'>$name</a></td>
</tr>
tpl;

        $categoryTree = $portalCategoryModel->adminCategoryTableTree($selectedIds, $tpl);

        $categories = $portalCategoryModel->where('delete_time', 0)->select();

        $this->assign('categories', $categories);
        $this->assign('selectedIds', $selectedIds);
        $this->assign('categories_tree', $categoryTree);

        return $this->fetch();
    }

    /**
     * 文章分类排序.
     *
     * @adminMenu(
     *     'name'   => '文章分类排序',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '文章分类排序',
     *     'param'  => ''
     * )
     */
    public function listOrder()
    {
        parent::listOrders(Db::name('portal_category'));
        $this->success('排序更新成功！', '');
    }

    /**
     * 文章分类显示隐藏.
     *
     * @adminMenu(
     *     'name'   => '文章分类显示隐藏',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '文章分类显示隐藏',
     *     'param'  => ''
     * )
     */
    public function toggle()
    {
        $data = $this->request->param();
        $portalCategoryModel = new PortalCategoryModel();
        $ids = $this->request->param('ids/a');

        if (isset($data['ids']) && !empty($data['display'])) {
            $portalCategoryModel->where('id', 'in', $ids)->update(['status' => 1]);
            $this->success('更新成功！');
        }

        if (isset($data['ids']) && !empty($data['hide'])) {
            $portalCategoryModel->where('id', 'in', $ids)->update(['status' => 0]);
            $this->success('更新成功！');
        }
    }

    /**
     * 删除文章分类.
     *
     * @adminMenu(
     *     'name'   => '删除文章分类',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除文章分类',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $portalCategoryModel = new PortalCategoryModel();
        $id = $this->request->param('id');
        //获取删除的内容
        $findCategory = $portalCategoryModel->where('id', $id)->find();

        if (empty($findCategory)) {
            $this->error('分类不存在!');
        }
        //判断此分类有无子分类（不算被删除的子分类）
        $categoryChildrenCount = $portalCategoryModel->where(['parent_id' => $id, 'delete_time' => 0])->count();

        if ($categoryChildrenCount > 0) {
            $this->error('此分类有子类无法删除!');
        }

        $categoryPostCount = Db::name('portal_category_post')->where('category_id', $id)->count();

        if ($categoryPostCount > 0) {
            $this->error('此分类有文章无法删除!');
        }

        $data = [
            'object_id'   => $findCategory['id'],
            'create_time' => time(),
            'table_name'  => 'portal_category',
            'name'        => $findCategory['name'],
        ];
        $result = $portalCategoryModel
            ->where('id', $id)
            ->update(['delete_time' => time()]);
        if ($result) {
            Db::name('recycleBin')->insert($data);
            $this->success('删除成功!');
        } else {
            $this->error('删除失败');
        }
    }
}
