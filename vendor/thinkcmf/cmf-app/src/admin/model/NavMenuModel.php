<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\admin\model;

use think\Exception;
use think\Model;
use tree\Tree;

class NavMenuModel extends Model
{
    /**
     * 模型名称
     * @var string
     */
    protected $name = 'nav_menu';

    /**
     * 获取某导航下所有菜单树形结构数组
     * @param int $navId    导航id
     * @param int $maxLevel 最大获取层级,默认不限制
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function navMenusTreeArray($navId = 0, $maxLevel = 0)
    {
        if (empty($navId)) {
            $navId = NavModel::where('is_main', 1)->value('id');
        }
        $navMenus     = $this->where('nav_id', $navId)->where('status', 1)->order('list_order ASC')->select()->toArray();
        $navMenusTree = [];
        if (!empty($navMenus)) {
            $tree = new Tree();
            $this->parseNavMenu4Home($navMenus);
            $tree->init($navMenus);

            $navMenusTree = $tree->getTreeArray(0, $maxLevel);
        }

        return $navMenusTree;
    }

    /**
     * 获取某导航菜单下的所有子菜单树形结构数组
     * @param $menuId 导航菜单 id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function subNavMenusTreeArray($menuId)
    {

        $navId = $this->where('id', $menuId)->where('status', 1)->value('nav_id');

        if (empty($navId)) {
            return [];
        }

        $navMenus = $this->where('nav_id', $navId)->where('status', 1)->order('list_order ASC')->select()->toArray();

        $navMenusTree = [];
        if (!empty($navMenus)) {
            $tree = new Tree();

            $this->parseNavMenu4Home($navMenus);
            $tree->init($navMenus);

            $navMenusTree = $tree->getTreeArray($menuId);
        }

        return $navMenusTree;
    }

    private function parseNavMenu4Home(&$navMenus)
    {
        foreach ($navMenus as $key => $navMenu) {
            $href    = htmlspecialchars_decode($navMenu['href']);
            $hrefOld = $href;
            if (strpos($hrefOld, "{") !== false) {
                $href = json_decode($navMenu['href'], true);
                $href = cmf_url($href['action'], $href['param']);
            } else {
                if ($hrefOld == "home") {
                    $href = request()->root() . "/";
                } else {
                    $href = $hrefOld;
                }
            }
            $navMenu['href'] = $href;
            $navMenus[$key]  = $navMenu;
        }
    }

    /**
     * 获取共享nav模板结构
     * @return array
     */
    public function selectNavs()
    {

        $tree       = new Tree();
        $tree->icon = ['&nbsp;│ ', '&nbsp;├─ ', '&nbsp;└─ '];
        $tree->nbsp = '&nbsp;';

        $navs = $this->getNavData();

        foreach ($navs as $key => $navData) {
            $tree->init($navData['items']);
            $tpl                = "<option value='\$rule' data-name='\$name'>\$spacer\$name</option>";
            $html               = $tree->getTree(0, $tpl);
            $navs[$key]['html'] = $html;
        }

        return $navs;

    }

    /**
     * 获取共享nav数据
     * @return array
     */
    private function getNavData()
    {
        $apps = cmf_scan_dir(APP_PATH . "*");

        array_push($apps, 'admin', 'user');

        $navs = [];
        foreach ($apps as $app) {

            if (is_dir(APP_PATH . $app)) {
                if (!(strpos($app, ".") === 0)) {
                    $navConfigFile = cmf_get_app_config_file($app, 'nav');
                    if (file_exists($navConfigFile)) {
                        $navApis = include $navConfigFile;

                        if (is_array($navApis) && !empty($navApis)) {
                            foreach ($navApis as $navApi) {

                                if (!empty($navApi['api'])) {
                                    try {
                                        $navData = action($app . '/' . $navApi['api'], [], 'api');
                                    } catch (Exception $e) {
                                        $navData = null;
                                    }

                                    if (!empty($navData) && !empty($navData['rule']) && count($navData['items']) > 0) {
                                        $this->parseNavData($navData, $navApi);

                                        if (!empty($navData['items'])) {
                                            array_push($navs, $navData);
                                        }
                                    }


                                }

                            }
                        }

                    }

                }
            }
        }
        return $navs;
    }

    /**
     * 解析导航数据
     * @param $navData
     * @param $navApi
     */
    private function parseNavData(&$navData, $navApi)
    {
        //TODO 检查导航数据合法性
        if (!empty($navData) && !empty($navData['rule']) && count($navData['items']) > 0) {
            $navData['name'] = $navApi['name'];
            $urlRule         = $navData['rule'];

            $items = $navData['items'];

            $navData['items'] = [];

            if ($items instanceof \think\Collection) {
                $items = $items->toArray();
            }

            foreach ($items as $item) {
                $rule           = [];
                $rule['action'] = $urlRule['action'];
                $rule['param']  = [];
                if (isset($urlRule['param'])) {
                    foreach ($urlRule['param'] as $key => $val) {
                        $rule['param'][$key] = $item[$val];
                    }
                }

                array_push($navData['items'], [
                    "name"      => $item['name'],
                    "url"       => url($rule['action'], $rule['param']),
                    "rule"      => base64_encode(json_encode($rule)),
                    "parent_id" => empty($item['parent_id']) ? 0 : $item['parent_id'],
                    "id"        => $item['id'],
                ]);

            }

        }
    }

}
