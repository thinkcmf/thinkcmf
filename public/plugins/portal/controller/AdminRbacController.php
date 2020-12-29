<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace plugins\portal\controller;

use cmf\controller\PluginAdminBaseController;
use plugins\portal\model\PortalCategoryModel;
use think\Db;
use app\admin\model\AdminMenuModel;

class AdminRbacController extends PluginAdminBaseController
{

    /**
     * 设置角色权限
     * @adminMenu(
     *     'name'   => '设置角色权限',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '设置角色权限',
     *     'param'  => ''
     * )
     * @return mixed
     */
    public function authorize()
    {
        $content = hook_one('admin_rbac_authorize_view');

        if (!empty($content)) {
            return $content;
        }

        $AuthAccess     = Db::name("AuthAccess");
        $adminMenuModel = new AdminMenuModel();
        //角色ID
        $roleId = $this->request->param("id", 0, 'intval');
        if (empty($roleId)) {
            $this->error("参数错误！");
        }

        $findOnlySelfArticlesAuthAccess = Db::name("auth_access")->where("role_id", $roleId)
            ->where('type', 'portal_only_self_articles')->find();

        $portalCategoryModel = new PortalCategoryModel();
        $keyword             = $this->request->param('keyword');

        $categoryTree = $portalCategoryModel->adminAuthorizeCategoryTableTree(0, '', $roleId);
        $this->assign('category_tree', $categoryTree);

        $this->assign('only_self_articles', $findOnlySelfArticlesAuthAccess);

        $this->assign('keyword', $keyword);
        $this->assign("role_id", $roleId);
        return $this->fetch();
    }

    /**
     * 角色授权提交
     * @adminMenu(
     *     'name'   => '角色授权提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '角色授权提交',
     *     'param'  => ''
     * )
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function authorizePost()
    {
        if ($this->request->isPost()) {
            $roleId = $this->request->param("role_id", 0, 'intval');
            if (!$roleId) {
                $this->error("需要授权的角色不存在！");
            }
            $categoryModel = new PortalCategoryModel();

            $categories = $categoryModel->select();
            foreach ($categories as $category) {
                $ruleName = "portal/Category/index?id={$category['id']}";
                $findRule = Db::name("auth_rule")->where('name', $ruleName)->find();

                if (empty($findRule)) {
                    Db::name("auth_rule")->insert([
                        'status' => 1,
                        'app'    => 'portal',
                        'type'   => 'portal_category',
                        'name'   => $ruleName,
                        'title'  => $category['name']
                    ]);
                } else {
                    Db::name("auth_rule")->where('id', $findRule['id'])->update([
                        'status' => 1,
                        'app'    => 'portal',
                        'type'   => 'portal_category',
                        'name'   => $ruleName,
                        'title'  => $category['name']
                    ]);
                }
            }

            $onlySelfArticles = $this->request->param('only_self_articles', 0);

            if (empty($onlySelfArticles)) {
                Db::name("auth_access")->where("role_id", $roleId)
                    ->where('type', 'portal_only_self_articles')->delete();
            } else {
                $findOnlySelfArticlesAuthAccess = Db::name("auth_access")->where("role_id", $roleId)
                    ->where('type', 'portal_only_self_articles')->find();

                if (empty($findOnlySelfArticlesAuthAccess)) {
                    Db::name("auth_access")->insert(["role_id" => $roleId, "rule_name" => "portal/Article/only_self_articles", 'type' => 'portal_only_self_articles']);
                }

                $findOnlySelfArticlesAuthRule = Db::name("auth_rule")
                    ->where('name', 'portal/Article/only_self_articles')->find();

                if (empty($findOnlySelfArticlesAuthRule)) {
                    Db::name("auth_rule")->insert([
                        'status'=>1,
                        'app'=>'portal',
                        'type'=>'portal_only_self_articles',
                        'name'=>'portal/Article/only_self_articles',
                        'title'=>'只能看到自己的文章'
                    ]);
                }
            }

            $ids = $this->request->param('ids/a');
            if (!empty($ids)) {

                Db::name("auth_access")->where(["role_id" => $roleId, 'type' => 'portal_category'])->delete();
                foreach ($ids as $id) {
                    $ruleName = "portal/Category/index?id={$id}";
                    Db::name("auth_access")->insert(["role_id" => $roleId, "rule_name" => $ruleName, 'type' => 'portal_category']);
                }

                $this->success("授权成功！");
            } else {
                //当没有数据时，清除当前角色授权
                Db::name("auth_access")->where("role_id", $roleId)->where('type', 'portal_category')->delete();
//                $this->error("没有接收到数据，执行清除授权成功！");
                $this->success("授权成功！");
            }
        }
    }

    /**
     * 检查指定菜单是否有权限
     * @param array $menu menu表中数组
     * @param       $privData
     * @return bool
     */
    private function _isChecked($menu, $privData)
    {
        $app    = $menu['app'];
        $model  = $menu['controller'];
        $action = $menu['action'];
        $name   = strtolower("$app/$model/$action");
        if ($privData) {
            if (in_array($name, $privData)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    /**
     * 获取菜单深度
     * @param       $id
     * @param array $array
     * @param int   $i
     * @return int
     */
    protected function _getLevel($id, $array = [], $i = 0)
    {
        if ($array[$id]['parent_id'] == 0 || empty($array[$array[$id]['parent_id']]) || $array[$id]['parent_id'] == $id) {
            return $i;
        } else {
            $i++;
            return $this->_getLevel($array[$id]['parent_id'], $array, $i);
        }
    }

    //角色成员管理
    public function member()
    {
        //TODO 添加角色成员管理

    }

}

