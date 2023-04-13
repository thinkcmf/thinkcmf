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
use think\Container;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Response;
use think\response\Redirect;
use think\Validate;


use think\facade\Db;
use think\View;
use think\facade\Config;

class BaseController
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

    protected $view;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 前置操作方法列表（即将废弃）
     * @var array $beforeActionList
     */
    protected $beforeActionList = [];

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

        if (!cmf_is_installed() && strpos($this->request->url(), 'install') === false) {
            return $this->redirect(cmf_get_root() . '/?s=install');
        }

        $this->view = new View($app);
        $this->_initializeView();

        // 控制器初始化
        $this->initialize();

        // 前置操作方法 即将废弃
        foreach ((array)$this->beforeActionList as $method => $options) {
            is_numeric($method) ?
                $this->beforeAction($options) :
                $this->beforeAction($method, $options);
        }
        $lastTime    = session('last_time');
        $currentTime = time();
        if (empty($lastTime) || $lastTime + 60 < $currentTime) {
            session('last_time', $currentTime);
        }
    }

    // 初始化
    protected function initialize()
    {
    }


    // 初始化视图配置
    protected function _initializeView()
    {
    }

    /**
     * 加载模板输出
     * @access protected
     * @param string $template 模板文件名
     * @param array  $vars     模板输出变量
     * @param array  $config   模板参数
     * @return mixed
     */
    protected function fetch($template = '', $vars = [], $config = [])
    {
        return $this->view->fetch($template, $vars, $config);;
    }

    /**
     * 渲染内容输出
     * @access protected
     * @param string $content 模板内容
     * @param array  $vars    模板输出变量
     * @param array  $config  模板参数
     * @return mixed
     */
    protected function display($content = '', $vars = [], $config = [])
    {
        return Response::create($content, 'view')->assign($vars)->isContent(true);
    }

    /**
     * 模板变量赋值
     * @access protected
     * @param mixed $name  要显示的模板变量
     * @param mixed $value 变量的值
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
     * @param array        $data     数据
     * @param string|array $validate 验证器名或者验证规则数组
     * @param array        $message  提示信息
     * @param bool         $batch    是否批量验证
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
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate . 'Validate');
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
     * 验证数据并直接提示错误信息
     * @access protected
     * @param array        $data     数据
     * @param string|array $validate 验证器名或者验证规则数组
     * @param array        $message  提示信息
     * @param mixed        $callback 回调方法（闭包）
     * @return array|string|true
     * @throws HttpResponseException
     */
    protected function validateFailError($data, $validate, $message = [], $callback = null)
    {
        $result = $this->validate($data, $validate, $message);
        if ($result !== true) {
            $this->error($result);
        }

        return $result;
    }

    /**
     *  排序 排序字段为list_orders数组 POST 排序字段为：list_order
     */
    protected function listOrders($model)
    {
        if ($this->request->isPost()) {
            $modelName = '';
            if (is_object($model)) {
                $modelName = $model->getName();
            } else {
                $modelName = $model;
            }

            $pk  = Db::name($modelName)->getPk(); //获取主键名称
            $ids = $this->request->post('list_orders/a');

            if (!empty($ids)) {
                foreach ($ids as $key => $r) {
                    $data['list_order'] = $r;
                    Db::name($modelName)->where($pk, $key)->update($data);
                }
            }
            return true;
        } else {
            return false;
        }

    }

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param mixed   $msg    提示信息
     * @param string  $url    跳转的URL地址
     * @param mixed   $data   返回的数据
     * @param integer $wait   跳转等待时间
     * @param array   $header 发送的Header信息
     * @return void
     */
    protected function success($msg = '', $url = null, $data = '', $wait = 3, array $header = [])
    {
        $type = $this->getResponseType();
        if (is_null($url)) {
            if (isset($_SERVER['HTTP_REFERER'])) {
                $url = $_SERVER['HTTP_REFERER'];
            } else {
                if ($type == 'html') {
                    $url = 'javascript:history.back(-1);';
                } else {
                    $url = '';
                }
            }

        } elseif ('' !== $url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : url($url);
        }

        $result = [
            'code' => 1,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];


        // 把跳转模板的渲染下沉，这样在 response_send 行为里通过getData()获得的数据是一致性的格式
        if ('html' == strtolower($type)) {
            $type = 'view';
        }

        if ($type == 'view') {
            $response = Response::create($this->app->config->get('app.dispatch_success_tmpl'), $type)->assign($result)->header($header);
        } else {
            $response = Response::create($result, $type)->header($header);
        }

        throw new HttpResponseException($response);
    }

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param mixed   $msg    提示信息
     * @param string  $url    跳转的URL地址
     * @param mixed   $data   返回的数据
     * @param integer $wait   跳转等待时间
     * @param array   $header 发送的Header信息
     * @return void
     */
    protected function error($msg = '', $url = null, $data = '', $wait = 3, array $header = [])
    {
        $type = $this->getResponseType();
        if (is_null($url)) {
            $url = $this->request->isAjax() ? '' : 'javascript:history.back(-1);';
        } elseif ('' !== $url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : url($url);
        }

        $result = [
            'code' => 0,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];

        if ('html' == strtolower($type)) {
            $type = 'view';
        }

        if ($type == 'view') {
            $response = Response::create($this->app->config->get('app.dispatch_error_tmpl'), $type)->assign($result)->header($header);
        } else {
            $response = Response::create($result, $type)->header($header);
        }

        throw new HttpResponseException($response);
    }

    /**
     * 返回封装后的API数据到客户端
     * @access protected
     * @param mixed   $data   要返回的数据
     * @param integer $code   返回的code
     * @param mixed   $msg    提示信息
     * @param string  $type   返回数据格式
     * @param array   $header 发送的Header信息
     * @return void
     */
    protected function result($data, $code = 0, $msg = '', $type = '', array $header = [])
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'time' => time(),
            'data' => $data,
        ];

        $type     = $type ?: $this->getResponseType();
        $response = Response::create($result, $type)->header($header);

        throw new HttpResponseException($response);
    }

    /**
     * URL重定向
     * @access protected
     * @param string  $url  跳转的URL表达式
     * @param integer $code http code
     * @param array   $with 隐式传参
     * @return void
     */
    protected function redirect($url, $code = 302, $with = [])
    {
        $response = Response::create($url, 'redirect');

        $response->code($code)->with($with);

        throw new HttpResponseException($response);
    }

    /**
     * 获取当前的response 输出类型
     * @access protected
     * @return string
     */
    protected function getResponseType()
    {
        if (!$this->app) {
            $this->app = Container::get('app');
        }

        $isAjax = $this->request->isAjax();
        $config = $this->app['config'];

        return $isAjax || $this->request->isJson()
            ? 'json'
            : 'html';
    }

}
