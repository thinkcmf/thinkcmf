<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\admin\model;

use think\Model;

class RouteModel extends Model
{
    /**
     * 获取所有url美化规则
     * @param boolean $refresh 是否强制刷新
     * @return mixed|void|boolean|NULL|unknown[]|unknown
     */
    public function getRoutes($refresh = false)
    {
        $routes = cache("routes");

        $appUrls = $this->getAppUrls();

        if ((!empty($routes) || is_array($routes)) && !$refresh) {
            return $routes;
        }
        $routes      = $this->where("status", 1)->order("list_order asc")->select();
        $allRoutes   = [];
        $cacheRoutes = [];
        foreach ($routes as $er) {
            $fullUrl = htmlspecialchars_decode($er['full_url']);

            // 解析URL
            $info = parse_url($fullUrl);

            $path = explode("/", $info['path']);
            if (count($path) != 3) {//必须是完整 url
                continue;
            }

            $module = strtolower($path[0]);

            // 解析参数
            $vars = [];
            if (isset($info['query'])) { // 解析地址里面参数 合并到vars
                parse_str($info['query'], $params);
                $vars = array_merge($params, $vars);
            }

            $vars_src = $vars;

            ksort($vars);

            $path = $info['path'];

            $fullUrl = $path . (empty($vars) ? "" : "?") . http_build_query($vars);

            $url = htmlspecialchars_decode($er['url']);

            if (isset($cacheRoutes[$path])) {
                array_push($cacheRoutes[$path], ['vars' => $vars]);
            } else {
                $cacheRoutes[$path] = [];
                array_push($cacheRoutes[$path], ['vars' => $vars]);
            }

            //$cacheRoutes[$fullUrl] = true;

//            if (strpos($url, ':') === false) {
//                $cacheRoutes['static'][$fullUrl] = $url;
//            } else {
//                $cacheRoutes['dynamic'][$path][] = ["query" => $vars, "url" => $url];
//            }
            if (empty($appUrls[$path]['pattern'])) {
                $allRoutes[$url] = $fullUrl;
            } else {
                $allRoutes[$url] = [$fullUrl, [], $appUrls[$path]['pattern']];
            }

        }
        cache("routes", $cacheRoutes);
        $route_dir = CMF_ROOT . "data/conf/";
        if (!file_exists($route_dir)) {
            mkdir($route_dir);
        }

        $route_file = $route_dir . "route.php";

        file_put_contents($route_file, "<?php\treturn " . stripslashes(var_export($allRoutes, true)) . ";");

        return $cacheRoutes;
    }

    public function getAppUrls()
    {
        $apps = cmf_scan_dir(APP_PATH . '*', GLOB_ONLYDIR);

        $appUrls = [];

        foreach ($apps as $app) {
            $urlConfigFile = APP_PATH . $app . '/url.php';
            if (file_exists($urlConfigFile)) {
                $urls = include $urlConfigFile;
                foreach ($urls as $action => $url) {
                    $action = $app . '/' . $action;

                    $appUrls[$action] = $url;
                    if (!empty($url['vars'])) {
                        foreach ($url['vars'] as $urlVarName => $urlVar) {
                            $appUrls[$action]['pattern'][$urlVarName] = $urlVar['pattern'];
                        }
                    }

                }
            }
        }

        return $appUrls;
    }

    public function getUrl($action, $vars)
    {
        $fullUrl = $this->buildFullUrl($action, $vars);

        $url = $this->where('full_url', $fullUrl)->value('url');

        return empty($url) ? '' : $url;
    }

    public function buildFullUrl($action, $vars)
    {
        // 解析参数
        if (is_string($vars)) {
            // aaa=1&bbb=2 转换成数组
            parse_str($vars, $vars);
        }

        if (!empty($vars)) {
            ksort($vars);

            $fullUrl = $action . '?' . http_build_query($vars);
        } else {
            $fullUrl = $action;
        }

        return $fullUrl;
    }

    public function exists($url, $fullUrl)
    {

        $findRouteCount = $this->where(['url' => $url, 'full_url' => ['neq', $fullUrl]])->count();

        return $findRouteCount > 0 ? true : false;
    }

    public function setRoute($url, $action, $vars, $type = 2, $listOrder = 10000)
    {
        $fullUrl   = $this->buildFullUrl($action, $vars);
        $findRoute = $this->where(['full_url' => $fullUrl])->find();

        if ($findRoute) {
            if (empty($url)) {
                $this->where(['id' => $findRoute['id']])->delete();
            } else {
                $this->where(['id' => $findRoute['id']])->update(['url' => $url, 'list_order' => $listOrder, 'type' => $type]);
            }
        } else {
            if (!empty($url)) {
                $this->insert(['full_url' => $fullUrl, 'url' => $url, 'list_order' => $listOrder, 'type' => $type]);
            }
        }
    }

    public function deleteRoute($action, $vars)
    {
        $fullUrl = $this->buildFullUrl($action, $vars);
        $this->where(['full_url' => $fullUrl])->delete();
        return true;
    }


}