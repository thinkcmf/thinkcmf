<?php

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

            $allRoutes[$url] = $fullUrl;

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

}