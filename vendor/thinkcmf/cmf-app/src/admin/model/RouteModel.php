<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
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
     * @return array|mixed|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
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

            $vars = [];
            // 解析参数
            if (isset($info['query'])) { // 解析地址里面参数 合并到vars
                parse_str($info['query'], $vars);
                ksort($vars);
            }


            if (isset($info['scheme'])) { //插件
                $plugin     = cmf_parse_name($info['scheme']);
                $controller = cmf_parse_name($info['host']);
                $action     = trim(strtolower($info['path']), '/');

                $pluginParams = [
                    '_plugin'     => $plugin,
                    '_controller' => $controller,
                    '_action'     => $action,
                ];

                $path = '\\cmf\\controller\\PluginController@index?' . http_build_query($pluginParams);

                $fullUrl = $path . (empty($vars) ? '' : '&') . http_build_query($vars);

            } else { // 应用
                $path = explode("/", $info['path']);
                if (count($path) != 3) {//必须是完整 url
                    continue;
                }

                $path = $info['path'];

                $fullUrl = $path . (empty($vars) ? "" : "?") . http_build_query($vars);
            }

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

        if (strpos(cmf_version(), '5.0.') === false) {
            $routeDir = CMF_DATA . "route/"; // 5.1
        } else {
            $routeDir = CMF_DATA . "conf/"; // 5.0
        }

        if (!file_exists($routeDir)) {
            mkdir($routeDir);
        }

        $route_file = $routeDir . "route.php";

        file_put_contents($route_file, "<?php\treturn " . var_export($allRoutes, true) . ";");

        return $cacheRoutes;
    }

    /**
     * @return array
     */
    public function getAppUrls()
    {
        $apps = cmf_scan_dir(APP_PATH . '*', GLOB_ONLYDIR);

        array_push($apps, 'admin', 'user');

        $appUrls = [];

        foreach ($apps as $app) {
            $urlConfigFile = cmf_get_app_config_file($app, 'url');

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

    public function getFullUrlByUrl($url)
    {
        $full_url = $this->where('url', $url)->value('full_url');

        return empty($full_url) ? '' : $full_url;

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

    public function existsRoute($url, $fullUrl)
    {
        $findRouteCount = $this->where('url', $url)->where('full_url', 'neq', $fullUrl)->count();

        return $findRouteCount > 0 ? true : false;
    }

    public function setRoute($url, $action, $vars, $type = 2, $listOrder = 10000)
    {
        $fullUrl   = $this->buildFullUrl($action, $vars);
        $findRoute = $this->where('full_url', $fullUrl)->find();

        if (preg_match("/[()'\";]/", $url)) {
            return false;
        }

        if ($findRoute) {
            if (empty($url)) {
                $this->where('id', $findRoute['id'])->delete();
            } else {
                $this->where('id', $findRoute['id'])->update([
                    'url'        => $url,
                    'list_order' => $listOrder,
                    'type'       => $type
                ]);
            }
        } else {
            if (!empty($url)) {
                $this->insert([
                    'full_url'   => $fullUrl,
                    'url'        => $url,
                    'list_order' => $listOrder,
                    'type'       => $type
                ]);
            }
        }
    }

    /**
     * @param $action
     * @param $vars
     * @return bool
     * @throws \Exception
     */
    public function deleteRoute($action, $vars)
    {
        $fullUrl = $this->buildFullUrl($action, $vars);
        $this->where('full_url', $fullUrl)->delete();
        return true;
    }


}