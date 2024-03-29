<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace cmf\controller;

use think\App;
use think\exception\ValidateException;
use think\Request;
use think\Validate;

class PluginRestBaseController extends RestBaseController
{

    /**
     * @var \cmf\lib\Plugin
     */
    private $plugin;

    /**
     * 构造函数
     * @param Request $request Request对象
     * @access public
     */
    public function __construct(App $app = null)
    {
        parent::__construct($app);

        $this->getPlugin();
    }

    // 初始化
    protected function initialize()
    {
        hook('home_init');
    }

    public function getPlugin()
    {

        if (is_null($this->plugin)) {
            $pluginName   = $this->request->param('_plugin');
            $pluginName   = cmf_parse_name($pluginName, 1);
            $class        = cmf_get_plugin_class($pluginName);
            $this->plugin = new $class;
        }

        return $this->plugin;

    }

    /**
     * 验证数据
     * @access protected
     * @param array        $data     数据
     * @param string|array $validate 验证器名或者验证规则数组
     * @param array        $message  提示信息
     * @param bool         $batch    是否批量验证
     * @param mixed        $callback 回调方法（闭包）
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate($data, $validate, $message = [], $batch = false, $callback = null)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : '\\plugins\\' . cmf_parse_name($this->plugin->getName()) . '\\validate\\' . $validate . 'Validate';
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        $result = $v->failException(false)->check($data);

        if (!$result) {
            $result = $v->getError();
        }

        return $result;
    }

    /**
     * 获取API路由路径
     * @return string 如demo/articles,demo/artilces/:id
     */
    public function getRoutePath(): string
    {
        $rule = $this->request->rule();
        $routeRuleName = $rule->getRule();

        if (empty($routeRuleName) || $routeRuleName == "plugin/<_plugin>/<_controller?>/<_action?>") {
            $pluginName = $this->request->param('_plugin');
            $pluginName = cmf_parse_name($pluginName, 0);
            $controller = $this->request->param('_controller');
            $controller = cmf_parse_name($controller, 0);
            $action     = $this->request->param('_action');
            $routePath  = "plugin/{$pluginName}/$controller/$action";
        } else {
            $routePath = preg_replace("/<([0-9a-zA-Z_]+)>/", ':$1', $rule->getRule());
            $routePath = str_replace('$', '', $routePath);
        }

        return $routePath;
    }


}
