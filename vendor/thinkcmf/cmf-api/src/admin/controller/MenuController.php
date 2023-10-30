<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace api\admin\controller;

use app\admin\logic\MenuLogic;
use app\admin\model\AdminMenuModel;
use app\admin\model\AuthRuleModel;
use app\admin\service\AdminMenuService;
use cmf\controller\RestAdminBaseController;
use OpenApi\Annotations as OA;
use think\facade\Cache;

class MenuController extends RestAdminBaseController
{
    /**
     * 后台首页菜单列表
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/home/menus",
     *     summary="后台首页菜单列表",
     *     description="后台首页菜单列表,常用于后台首页左侧菜单",
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "list":{
     *                  {"id": 113,"parent_id": 32,"type": 1,"status": 1,"list_order": 0,"app": "admin","controller": "Setting","action": "site","param": "","name": "网站信息","icon": "","remark": "网站信息"}
     *              },
     *              "total":100
     *          }})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function menus(AdminMenuService $adminMenuService)
    {
        $userId = $this->getUserId();
        $menus  = $adminMenuService->menus($userId);
        $this->success('success！', ['list' => $menus, 'total' => count($menus)]);
    }

    /**
     * 后台菜单列表
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/menus",
     *     summary="后台菜单列表",
     *     description="后台菜单列表",
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "list":{
     *                  {"id": 113,"parent_id": 32,"type": 1,"status": 1,"list_order": 0,"app": "admin","controller": "Setting","action": "site","param": "","name": "网站信息","icon": "","remark": "网站信息"}
     *              },
     *              "total":100
     *          }})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function index()
    {
        $menus = AdminMenuModel::order(["list_order" => "ASC"])->select();
        $this->success('success', ['list' => $menus, 'total' => $menus->count()]);
    }

    /**
     * 添加后台菜单
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/menus",
     *     summary="添加后台菜单",
     *     description="添加后台菜单",
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/AdminMenuSaveRequest")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AdminMenuSaveRequest")
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "item":{"id": 113,"parent_id": 32,"type": 1,"status": 1,"list_order": 0,"app": "admin","controller": "Setting","action": "site","param": "","name": "网站信息","icon": "","remark": "网站信息"}
     *          }})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function save()
    {
        if ($this->request->isPost()) {
            $result = $this->validate($this->request->param(), 'AdminMenu');
            if ($result !== true) {
                $this->error($result);
            } else {
                $data      = $this->request->param();
                $adminMenu = AdminMenuModel::create($data);

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
                $this->_exportAppMenuDefaultLang();
                Cache::clear('admin_menus');// 删除后台菜单缓存
                $this->success(lang('ADD_SUCCESS'), ['item' => $adminMenu]);
            }
        }
    }

    /**
     * 获取后台菜单信息
     * @throws \think\exception\DbException
     * @OA\Get(
     *     tags={"admin"},
     *     path="/admin/menus/{id}",
     *     summary="获取后台菜单信息",
     *     description="获取后台菜单信息",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="后台菜单id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "success","data":{
     *              "item":{"id": 113,"parent_id": 32,"type": 1,"status": 1,"list_order": 0,"app": "admin","controller": "Setting","action": "site","param": "","name": "网站信息","icon": "","remark": "网站信息"}
     *          }})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "not found!","data":""})
     *     ),
     * )
     */
    public function read($id)
    {
        $id        = $this->request->param("id", 0, 'intval');
        $adminMenu = AdminMenuModel::where("id", $id)->find();

        if (empty($adminMenu)) {
            $this->error('未找到菜单!');
        } else {
            $this->success('success', ['item' => $adminMenu]);
        }
    }

    /**
     * 编辑后台菜单
     * @throws \think\exception\DbException
     * @OA\Put(
     *     tags={"admin"},
     *     path="/admin/menus/{id}",
     *     summary="编辑后台菜单",
     *     description="编辑后台菜单",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="后台菜单id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/AdminMenuSaveRequest")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AdminMenuSaveRequest")
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "保存成功","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error!","data":""})
     *     ),
     * )
     */
    public function update($id)
    {
        if ($this->request->isPut()) {
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
     * 删除后台菜单
     * @throws \think\exception\DbException
     * @OA\Delete(
     *     tags={"admin"},
     *     path="/admin/menus/{id}",
     *     summary="删除后台菜单",
     *     description="删除后台菜单",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="后台菜单id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "删除成功!","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error","data":""})
     *     ),
     * )
     */
    public function delete($id)
    {
        if ($this->request->isDelete()) {
            $id    = $this->request->param("id", 0, 'intval');
            $count = AdminMenuModel::where("parent_id", $id)->count();
            if ($count > 0) {
                $this->error("该菜单下还有子菜单，无法删除！");
            }
            if (AdminMenuModel::destroy($id) !== false) {
                $this->success(lang('DELETE_SUCCESS'));
            } else {
                $this->error(lang('DELETE_FAILED'));
            }
        }
    }

    /**
     * 后台菜单排序
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/menus/list/order",
     *     summary="后台菜单排序",
     *     description="后台菜单排序",
     *     @OA\RequestBody(
     *         description="<b>请求参数</b>",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/ListOrdersRequestForm")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/ListOrdersRequest")
     *         ),
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "操作成功!","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error！","data":""})
     *     ),
     * )
     */
    public function listOrder()
    {
        $adminMenuModel = new  AdminMenuModel();
        parent::listOrders($adminMenuModel);
        $this->success(lang('Sort update successful'));
    }

    /**
     * 导出后台菜单语言包
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function _exportAppMenuDefaultLang()
    {
        $menus            = AdminMenuModel::order(["app" => "ASC", "controller" => "ASC", "action" => "ASC"])->select();
        $langDir          = cmf_current_lang();
        $adminMenuLang    = CMF_DATA . "lang/tmp/" . $langDir . "/admin_menu.php";
        $adminMenuLangDir = dirname($adminMenuLang);
        if (!is_dir($adminMenuLangDir)) {
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

    /**
     * 导出后台菜单语言包
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"admin"},
     *     path="/admin/menus/lang/export",
     *     summary="导出后台菜单语言包",
     *     description="导出后台菜单语言包",
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "操作成功!","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error！","data":""})
     *     ),
     * )
     */
    public function exportMenuLang()
    {
        $this->_exportAppMenuDefaultLang();
        $this->success('操作成功');
    }

    /**
     * 导入新后台菜单
     * @throws \think\exception\DbException
     * @OA\Post  (
     *     tags={"admin"},
     *     path="/admin/menus/import",
     *     summary="导入新后台菜单",
     *     description="导入新后台菜单",
     *     @OA\RequestBody(
     *         description="请求参数",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(ref="#/components/schemas/AdminMenuImportRequest")
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AdminMenuImportRequest")
     *         )
     *     ),
     *     @OA\Response(
     *          response="1",
     *          description="success",
     *          @OA\JsonContent(example={"code": 1,"msg": "操作成功!","data":""})
     *     ),
     *     @OA\Response(
     *          response="0",
     *          @OA\JsonContent(example={"code": 0,"msg": "error！","data":""})
     *     ),
     * )
     */
    public function importMenus()
    {
        $apps = cmf_scan_dir(APP_PATH . '*', GLOB_ONLYDIR);

        array_push($apps, 'admin', 'user');

        $apps = array_values(array_unique($apps));

        $app = $this->request->param('app', '');
        if (empty($app)) {
            $app = $apps[0];
        }


        if (!in_array($app, $apps)) {
            $this->error('应用' . $app . '不存在!');
        }

        $newMenus  = MenuLogic::importMenus($app);
        $index     = array_search($app, $apps);
        $nextIndex = $index + 1;
        $nextIndex = $nextIndex >= count($apps) ? 0 : $nextIndex;
        $next_app = "";
        if ($nextIndex) {
            $next_app = $apps[$nextIndex];
        }

        Cache::clear('admin_menus');// 删除后台菜单缓存

        $this->success('操作成功',["app"=>$app,"new_menus"=>$newMenus,"next_app"=>$next_app]);
    }

}
