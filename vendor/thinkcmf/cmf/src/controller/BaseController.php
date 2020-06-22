<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace cmf\controller;

use think\App;
use think\exception\ValidateException;
use think\Response;
use think\Validate;


use think\Container;
use think\Controller;
use think\Db;
use think\facade\View;
use think\facade\Config;

class BaseController extends Controller
{
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];


    /**
     * BaseController constructor.
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;

        if (!cmf_is_installed() && $this->request->module() != 'install') {
            return $this->redirect(cmf_get_root() . '/?s=install');
        }

        $this->_initializeView();
        $this->view = View::init(Config::get('template.'));

        // 控制器初始化
        $this->initialize();

        // 前置操作方法 即将废弃
        foreach ((array)$this->beforeActionList as $method => $options) {
            is_numeric($method) ?
                $this->beforeAction($options) :
                $this->beforeAction($method, $options);
        }

    }

    // 初始化
    protected function initialize()
    {}


    // 初始化视图配置
    protected function _initializeView()
    {
    }

    /**
     * 加载模板输出
     * @access protected
     * @param  string $template 模板文件名
     * @param  array  $vars     模板输出变量
     * @param  array  $config   模板参数
     * @return mixed
     */
    protected function fetch($template = '', $vars = [], $config = [])
    {
        return Response::create($template, 'view')->assign($vars)->config($config);
    }

    /**
     * 渲染内容输出
     * @access protected
     * @param  string $content 模板内容
     * @param  array  $vars    模板输出变量
     * @param  array  $config  模板参数
     * @return mixed
     */
    protected function display($content = '', $vars = [], $config = [])
    {
        return Response::create($content, 'view')->assign($vars)->config($config)->isContent(true);
    }

    /**
     * 模板变量赋值
     * @access protected
     * @param  mixed $name  要显示的模板变量
     * @param  mixed $value 变量的值
     * @return $this
     */
    protected function assign($name, $value = '')
    {
        $this->view->assign($name, $value);

        return $this;
    }

    /**
     * 验证数据
     * @access protected
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
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
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
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

        return $v->failException(true)->check($data);
    }

    /**
     *  排序 排序字段为list_orders数组 POST 排序字段为：list_order
     */
    protected function listOrders($model)
    {
        $modelName = '';
        if (is_object($model)) {
            $modelName = $model->getName();
        } else {
            $modelName = $model;
        }

        $pk  = Db::name($modelName)->getPk(); //获取主键名称
        $ids = $this->request->post("list_orders/a");

        if (!empty($ids)) {
            foreach ($ids as $key => $r) {
                $data['list_order'] = $r;
                Db::name($modelName)->where($pk, $key)->update($data);
            }
        }

        return true;
    }

}
