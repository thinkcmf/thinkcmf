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

use app\admin\model\PluginModel;
use think\Container;
use think\exception\ValidateException;
use think\facade\Config;
use think\Loader;
use think\template\exception\TemplateNotFoundException;
use think\Validate;

class PluginBaseController extends BaseController
{

    /**
     * @var \cmf\lib\Plugin
     */
    private $plugin;

    /**
     * 前置操作方法列表
     * @var array $beforeActionList
     * @access protected
     */
    protected $beforeActionList = [];

    /**
     * 构造函数
     * @access public
     */
    public function __construct()
    {
        $this->app     = app();
        $this->request = request();

        $this->getPlugin();

        $this->view = $this->plugin->getView();

        $siteInfo = cmf_get_site_info();

        $this->assign('site_info', $siteInfo);

        // 控制器初始化
        $this->initialize();

    }

    public function getPlugin()
    {

        if (is_null($this->plugin)) {
            $pluginName = $this->request->param('_plugin');
            $pluginName = cmf_parse_name($pluginName, 1);
            $class      = cmf_get_plugin_class($pluginName);


            //检查是否启用。非启用则禁止访问。
            $pluginModel = new PluginModel();
            $findPlugin  = $pluginModel->where('name', '=', $pluginName)->find();
            if (empty($findPlugin)) {
                $this->error('插件未安装!');
            }

            if ($findPlugin['status'] != 1) {
                $this->error('插件未启用!');
            }


            $this->plugin = new $class;
        }

        return $this->plugin;

    }

    // 初始化
    protected function initialize()
    {
    }

    /**
     * 加载模板输出(支持:/index/index,index/index,index,空,:index,/index)
     * @access protected
     * @param string $template 模板文件名
     * @param array  $vars     模板输出变量
     * @param array  $replace  模板替换
     * @param array  $config   模板参数
     * @return mixed|string
     * @throws \Exception
     */
    protected function fetch($template = '', $vars = [], $replace = [], $config = [])
    {
        $template = $this->parseTemplate($template);

        // 模板不存在 抛出异常
        if (!is_file($template)) {
            throw new TemplateNotFoundException('template not exists:' . $template, $template);
        }

        return $this->view->fetch($template, $vars, $replace, $config);
    }


    /**
     * 自动定位模板文件
     * @access private
     * @param string $template 模板文件规则
     * @return string
     */
    protected function parseTemplate($template)
    {
        // 分析模板文件规则
        $viewEngineConfig = config('view');

        $path = $this->plugin->getThemeRoot();

        $depr = $viewEngineConfig['view_depr'];

        $data       = $this->request->param();
        $controller = $data['_controller'];
        $action     = $data['_action'];

        if (0 !== strpos($template, '/')) {
            $template   = str_replace(['/', ':'], $depr, $template);
            $controller = cmf_parse_name($controller);
            if ($controller) {
                if ('' == $template) {
                    // 如果模板文件名为空 按照默认规则定位
                    $template = str_replace('.', DIRECTORY_SEPARATOR, $controller) . $depr . $action;
                } elseif (false === strpos($template, $depr)) {
                    $template = str_replace('.', DIRECTORY_SEPARATOR, $controller) . $depr . $template;
                }
            }
        } else {
            $template = str_replace(['/', ':'], $depr, substr($template, 1));
        }
        return $path . ltrim($template, '/') . '.' . ltrim($viewEngineConfig['view_suffix'], '.');
    }

    /**
     * 渲染内容输出
     * @access protected
     * @param string $content 模板内容
     * @param array  $vars    模板输出变量
     * @param array  $replace 替换内容
     * @param array  $config  模板参数
     * @return mixed
     */
    protected function display($content = '', $vars = [], $replace = [], $config = [])
    {
        return $this->view->display($content, $vars, $replace, $config);
    }

    /**
     * 模板变量赋值
     * @access protected
     * @param mixed $name  要显示的模板变量
     * @param mixed $value 变量的值
     * @return void
     */
    protected function assign($name, $value = '')
    {
        $this->view->assign($name, $value);
    }

    /**
     * 设置验证失败后是否抛出异常
     * @access protected
     * @param bool $fail 是否抛出异常
     * @return $this
     */
    protected function validateFailException($fail = true)
    {
        $this->failException = $fail;
        return $this;
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
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
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

}
