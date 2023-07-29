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

use cmf\model\UserTokenModel;
use think\App;
use think\Container;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Response;
use think\Validate;

class RestBaseController
{
    //token
    protected $token = '';

    //设备类型
    protected $deviceType = '';

    protected $apiVersion;

    //用户 id
    protected $userId = 0;

    //用户
    protected $user;

    protected $app;

    //用户类型
    protected $userType;

    protected $allowedDeviceTypes = ['mobile', 'android', 'iphone', 'ipad', 'web', 'pc', 'mac', 'wxapp', 'ios'];

    /**
     * @var \think\Request Request实例
     */
    protected $request;
    // 验证失败是否抛出异常
    protected $failException = false;
    // 是否批量验证
    protected $batchValidate = false;

    /**
     * 前置操作方法列表
     * @var array $beforeActionList
     * @access protected
     */
    protected $beforeActionList = [];

    /**
     * RestBaseController constructor.
     * @param App|null $app
     */
    public function __construct(App $app = null)
    {
        $this->app     = $app ?: app();
        $this->request = $this->app->request;

//        $this->request->root(cmf_get_root() . '/');

        $this->apiVersion = $this->request->header('XX-Api-Version', '1.1.0');

        // 用户验证初始化
        $this->_initUser();

        // 控制器初始化
        $this->initialize();

        // 前置操作方法
        if ($this->beforeActionList) {
            foreach ($this->beforeActionList as $method => $options) {
                is_numeric($method) ?
                    $this->beforeAction($options) :
                    $this->beforeAction($method, $options);
            }
        }
    }

    // 初始化
    protected function initialize()
    {
    }

    private function _initUser()
    {
        $token = $this->request->header('Authorization', '');
        if (substr($token, 0, 7) === 'Bearer ') {
            $token = substr($token, 7);
        }
        if (empty($token)) {
            $token = $this->request->header('XX-Token');
        }
        $deviceType = $this->request->header('XX-Device-Type');

        if (empty($deviceType)) {
            return;
        }

        if (!in_array($deviceType, $this->allowedDeviceTypes)) {
            return;
        }

        $this->deviceType = $deviceType;

        if (empty($token)) {
            return;
        }

        $this->token = $token;

        $user = UserTokenModel::alias('a')
            ->field('b.*')
            ->where(['token' => $token, 'device_type' => $deviceType])
            ->join('user b', 'a.user_id = b.id')
            ->find();

        if (!empty($user)) {
            $this->user     = $user;
            $this->userId   = $user['id'];
            $this->userType = $user['user_type'];
        }

    }

    /**
     * 前置操作
     * @access protected
     * @param string $method  前置操作方法名
     * @param array  $options 调用参数 ['only'=>[...]] 或者['except'=>[...]]
     */
    protected function beforeAction($method, $options = [])
    {
        if (isset($options['only'])) {
            if (is_string($options['only'])) {
                $options['only'] = explode(',', $options['only']);
            }
            if (!in_array($this->request->action(), $options['only'])) {
                return;
            }
        } elseif (isset($options['except'])) {
            if (is_string($options['except'])) {
                $options['except'] = explode(',', $options['except']);
            }
            if (in_array($this->request->action(), $options['except'])) {
                return;
            }
        }

        call_user_func([$this, $method]);
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
     * @return bool
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
     * 操作成功跳转的快捷方法
     * @access protected
     * @param mixed $msg    提示信息
     * @param mixed $data   返回的数据
     * @param array $header 发送的Header信息
     * @return void
     */
    protected function success($msg = '', $data = '', array $header = [])
    {
        $code   = 1;
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ];

        $type     = $this->getResponseType();
        $response = Response::create($result, $type)->header($header);

        throw new HttpResponseException($response);
    }

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param mixed $msg    提示信息,若要指定错误码,可以传数组,格式为['code'=>您的错误码,'msg'=>'您的错误消息']
     * @param mixed $data   返回的数据
     * @param array $header 发送的Header信息
     * @return void
     */
    protected function error($msg = '', $data = '', array $header = [])
    {
        $code = 0;
        if (is_array($msg)) {
            $code = $msg['code'];
            $msg  = $msg['msg'];
        }
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ];

        $type     = $this->getResponseType();
        $response = Response::create($result, $type)->header($header);

        throw new HttpResponseException($response);
    }

    /**
     * 获取当前的response 输出类型
     * @access protected
     * @return string
     */
    protected function getResponseType()
    {
        return 'json';
    }

    /**
     * 获取当前登录用户的id
     * @return int
     */
    public function getUserId()
    {
        if (empty($this->userId)) {
            $this->error(['code' => 10001, 'msg' => '用户未登录']);
        }
        return $this->userId;
    }

    /**
     * 获取API路由路径
     * @return string 如demo/articles,demo/artilces/:id
     */
    public function getRoutePath()
    {
        $rule = $this->request->rule();

        if (empty($rule->getRule())) {
            $app        = $this->app->http->getName();
            $controller = cmf_parse_name($this->request->controller());
            $action     = $this->request->action(false);
            $routePath  = "$app/$controller/$action";
        } else {
            $routePath = preg_replace("/<(.+)>/", ':$1', $rule['rule']);
            $routePath = str_replace('$', '', $routePath);
        }

        return $routePath;
    }

}
