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

use app\admin\logic\MenuLogic;
use app\admin\model\AdminMenuModel;
use app\admin\model\AuthRuleModel;
use cmf\controller\AdminBaseController;
use think\facade\Cache;
use tree\Tree;

class MenuController extends AdminBaseController
{
    /**
     * 后台菜单管理
     * @adminMenu(
     *     'name'   => '后台菜单',
     *     'parent' => 'admin/Setting/default',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '后台菜单管理',
     *     'param'  => ''
     * )
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $content = hook_one('admin_menu_index_view');

        if (!empty($content)) {
            return $content;
        }

        session('admin_menu_index', 'Menu/index');
        $result     = AdminMenuModel::order(["list_order" => "ASC"])->select()->toArray();
        $tree       = new Tree();
        $tree->icon = ['&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─', '&nbsp;&nbsp;&nbsp;└─ '];
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';

        $newMenus = [];
        foreach ($result as $m) {
            $newMenus[$m['id']] = $m;
        }
        foreach ($result as $key => $value) {

            $result[$key]['parent_id_node'] = ($value['parent_id']) ? ' class="child-of-node-' . $value['parent_id'] . '"' : '';
            $result[$key]['style']          = empty($value['parent_id']) ? '' : 'display:none;';
            $result[$key]['str_manage']     = '<a class="btn btn-xs btn-primary" href="' . url("Menu/add", ["parent_id" => $value['id'], "menu_id" => $this->request->param("menu_id")]) . '">' . lang('ADD_SUB_MENU') . '</a> 
                                               <a class="btn btn-xs btn-primary" href="' . url("Menu/edit", ["id" => $value['id'], "menu_id" => $this->request->param("menu_id")]) . '">' . lang('EDIT') . '</a>  
                                               <a class="btn btn-xs btn-danger js-ajax-delete" href="' . url("Menu/delete", ["id" => $value['id'], "menu_id" => $this->request->param("menu_id")]) . '">' . lang('DELETE') . '</a> ';
            $result[$key]['status']         = $value['status'] ? '<span class="label label-success">' . lang('DISPLAY') . '</span>' : '<span class="label label-warning">' . lang('HIDDEN') . '</span>';
            $result[$key]['name_i18n']      = lang(strtoupper("{$value['app']}_{$value['controller']}_{$value['action']}"));
            if (APP_DEBUG) {
                $result[$key]['app'] = $value['app'] . "/" . $value['controller'] . "/" . $value['action'];
            }
        }

        $tree->init($result);
        $str      = "<tr id='node-\$id' \$parent_id_node style='\$style'>
                        <td style='padding-left:20px;'><input name='list_orders[\$id]' type='text' size='3' value='\$list_order' class='input input-order'></td>
                        <td>\$id</td>
                        <td>\$spacer\$name_i18n</td>
                        <td>\$app</td>
                        <td>\$status</td>
                        <td>\$str_manage</td>
                    </tr>";
        $category = $tree->getTree(0, $str);
        $this->assign("category", $category);
        return $this->fetch();
    }

    /**
     * 后台所有菜单列表
     * @adminMenu(
     *     'name'   => '所有菜单',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '后台所有菜单列表',
     *     'param'  => ''
     * )
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function lists()
    {
        session('admin_menu_index', 'Menu/lists');
        $result = AdminMenuModel::order(["app" => "ASC", "controller" => "ASC", "action" => "ASC"])->select();
        $this->assign("menus", $result);
        return $this->fetch();
    }

    /**
     * 后台菜单添加
     * @adminMenu(
     *     'name'   => '后台菜单添加',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '后台菜单添加',
     *     'param'  => ''
     * )
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function add()
    {
        $tree     = new Tree();
        $parentId = $this->request->param("parent_id", 0, 'intval');
        $result   = AdminMenuModel::order(["list_order" => "ASC"])->select()->toArray();
        $array    = [];
        foreach ($result as $r) {
            $r['selected'] = $r['id'] == $parentId ? 'selected' : '';
            $array[]       = $r;
        }
        $str = "<option value='\$id' \$selected>\$spacer \$name</option>";
        $tree->init($array);
        $selectCategory = $tree->getTree(0, $str);
        $this->assign("select_category", $selectCategory);
        return $this->fetch();
    }

    /**
     * 后台菜单添加提交保存
     * @adminMenu(
     *     'name'   => '后台菜单添加提交保存',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '后台菜单添加提交保存',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        if ($this->request->isPost()) {
            $result = $this->validate($this->request->param(), 'AdminMenu');
            if ($result !== true) {
                $this->error($result);
            } else {
                $data = $this->request->param();
                AdminMenuModel::strict(false)->field(true)->insert($data);

                $app          = $this->request->param("app");
                $controller   = $this->request->param("controller");
                $action       = $this->request->param("action");
                $param        = $this->request->param("param");
                $authRuleName = "$app/$controller/$action";
                $menuName     = $this->request->param("name");

                $findAuthRuleCount = AuthRuleModel::where([
                    'app'  => $app,
                    'name' => $authRuleName,
                    'type' => 'admin_url'
                ])->count();
                if (empty($findAuthRuleCount)) {
                    AuthRuleModel::insert([
                        "name"  => $authRuleName,
                        "app"   => $app,
                        "type"  => "admin_url", //type 1-admin rule;2-user rule
                        "title" => $menuName,
                        'param' => $param,
                    ]);
                }
                $sessionAdminMenuIndex = session('admin_menu_index');
                $to                    = empty($sessionAdminMenuIndex) ? "Menu/index" : $sessionAdminMenuIndex;
                $this->_exportAppMenuDefaultLang();
                Cache::clear('admin_menus');// 删除后台菜单缓存
                $this->success(lang('ADD_SUCCESS'), url($to));
            }
        }
    }

    /**
     * 后台菜单编辑
     * @adminMenu(
     *     'name'   => '后台菜单编辑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '后台菜单编辑',
     *     'param'  => ''
     * )
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function edit()
    {
        $tree      = new Tree();
        $id        = $this->request->param("id", 0, 'intval');
        $adminMenu = AdminMenuModel::where("id", $id)->find();
        $result    = AdminMenuModel::order(["list_order" => "ASC"])->select()->toArray();
        $array     = [];
        foreach ($result as $r) {
            $r['selected'] = $r['id'] == $adminMenu['parent_id'] ? 'selected' : '';
            $array[]       = $r;
        }
        $str = "<option value='\$id' \$selected>\$spacer \$name</option>";
        $tree->init($array);
        $selectCategory = $tree->getTree(0, $str);
        $this->assign("data", $adminMenu);
        $this->assign("select_category", $selectCategory);
        return $this->fetch();
    }

    /**
     * 后台菜单编辑提交保存
     * @adminMenu(
     *     'name'   => '后台菜单编辑提交保存',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '后台菜单编辑提交保存',
     *     'param'  => ''
     * )
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function editPost()
    {
        if ($this->request->isPost()) {
            $id      = $this->request->param('id', 0, 'intval');
            $oldMenu = AdminMenuModel::where('id', $id)->find();

            $result = $this->validate($this->request->param(), 'AdminMenu.edit');

            if ($result !== true) {
                $this->error($result);
            } else {
                AdminMenuModel::strict(false)->field(true)->update($this->request->param());
                $app          = $this->request->param("app");
                $controller   = $this->request->param("controller");
                $action       = $this->request->param("action");
                $param        = $this->request->param("param");
                $authRuleName = "$app/$controller/$action";
                $menuName     = $this->request->param("name");

                $findAuthRuleCount = AuthRuleModel::where([
                    'app'  => $app,
                    'name' => $authRuleName,
                    'type' => 'admin_url'
                ])->count();
                if (empty($findAuthRuleCount)) {
                    $oldApp        = $oldMenu['app'];
                    $oldController = $oldMenu['controller'];
                    $oldAction     = $oldMenu['action'];
                    $oldName       = "$oldApp/$oldController/$oldAction";
                    $findOldRuleId = AuthRuleModel::where("name", $oldName)->value('id');
                    if (empty($findOldRuleId)) {
                        AuthRuleModel::insert([
                            "name"  => $authRuleName,
                            "app"   => $app,
                            "type"  => "admin_url",
                            "title" => $menuName,
                            "param" => $param
                        ]);//type 1-admin rule;2-user rule
                    } else {
                        AuthRuleModel::where('id', $findOldRuleId)->update([
                            "name"  => $authRuleName,
                            "app"   => $app,
                            "type"  => "admin_url",
                            "title" => $menuName,
                            "param" => $param]);//type 1-admin rule;2-user rule
                    }
                } else {
                    AuthRuleModel::where([
                        'app'  => $app,
                        'name' => $authRuleName,
                        'type' => 'admin_url'
                    ])->update(["title" => $menuName, 'param' => $param]);//type 1-admin rule;2-user rule
                }
                $this->_exportAppMenuDefaultLang();
                Cache::clear('admin_menus');// 删除后台菜单缓存
                $this->success(lang('EDIT_SUCCESS'));
            }
        }
    }

    /**
     * 后台菜单删除
     * @adminMenu(
     *     'name'   => '后台菜单删除',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '后台菜单删除',
     *     'param'  => ''
     * )
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function delete()
    {
        if ($this->request->isPost()) {
            $id    = $this->request->param("id", 0, 'intval');
            $count = AdminMenuModel::where("parent_id", $id)->count();
            if ($count > 0) {
                $this->error(lang('ADMIN_SUBMENU_ERROR'));
            }
            if (AdminMenuModel::destroy($id) !== false) {
                $this->success(lang('ADMIN_MENU_DELETE_SUCCESS'));
            } else {
                $this->error(lang('DELETE_FAILED'));
            }
        }
    }

    /**
     * 后台菜单排序
     * @adminMenu(
     *     'name'   => '后台菜单排序',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '后台菜单排序',
     *     'param'  => ''
     * )
     */
    public function listOrder()
    {
        $adminMenuModel = new AdminMenuModel();
        parent::listOrders($adminMenuModel);
        $this->success(lang('SORT_SUCCESS'));
    }

    /**
     * 导入新后台菜单
     * @adminMenu(
     *     'name'   => '导入新后台菜单',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '导入新后台菜单',
     *     'param'  => ''
     * )
     * @return mixed
     * @throws \ReflectionException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function getActions()
    {
        $apps = cmf_scan_dir(APP_PATH . '*', GLOB_ONLYDIR);

        array_push($apps, 'admin', 'user');

        $apps = array_values(array_unique($apps));

        $app = $this->request->param('app', '');
        if (empty($app)) {
            $app = $apps[0];
        }

        if (!in_array($app, $apps)) {
            $this->error(lang('APP_NOT_EXIST', ['app' => $app]));
        }

        $newMenus  = MenuLogic::importMenus($app);
        $index     = array_search($app, $apps);
        $nextIndex = $index + 1;
        $nextIndex = $nextIndex >= count($apps) ? 0 : $nextIndex;
        if ($nextIndex) {
            $this->assign("next_app", $apps[$nextIndex]);
        }
        $this->assign("app", $app);
        $this->assign("new_menus", $newMenus);

        Cache::clear('admin_menus');// 删除后台菜单缓存

        return $this->fetch();

    }

    /**
     * 导出后台菜单语言包
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function _exportAppMenuDefaultLang()
    {
        $menus         = AdminMenuModel::order(["app" => "ASC", "controller" => "ASC", "action" => "ASC"])->select();
        $langDir       = cmf_current_lang();
        $adminMenuLang = CMF_DATA . "lang/" . $langDir . "/admin_menu.php";

        if (!empty($adminMenuLang) && !file_exists_case($adminMenuLang)) {
            mkdir(dirname($adminMenuLang), 0777, true);
        }

        $lang = [];

        foreach ($menus as $menu) {
            $lang_key        = strtoupper($menu['app'] . '_' . $menu['controller'] . '_' . $menu['action']);
            $lang[$lang_key] = $menu['name'];
        }

        $langStr = var_export($lang, true);
        $langStr = preg_replace("/\s+\d+\s=>\s(\n|\r)/", "\n", $langStr);

        if (!empty($adminMenuLang)) {
            file_put_contents($adminMenuLang, "<?php\nreturn $langStr;");
        }
    }
}
