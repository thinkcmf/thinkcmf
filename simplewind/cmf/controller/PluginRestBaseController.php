<?php

// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------

namespace cmf\controller;

use think\exception\ValidateException;
use think\Loader;
use think\Request;

class PluginRestBaseController extends RestBaseController
{
    /**
     * @var \cmf\lib\Plugin
     */
    private $plugin;

    /**
     * 构造函数.
     *
     * @param Request $request Request对象
     */
    public function __construct(Request $request = null)
    {
        parent::__construct($request);

        $this->getPlugin();
    }

    public function getPlugin()
    {
        if (is_null($this->plugin)) {
            $pluginName = $this->request->param('_plugin');
            $pluginName = cmf_parse_name($pluginName, 1);
            $class = cmf_get_plugin_class($pluginName);
            $this->plugin = new $class();
        }

        return $this->plugin;
    }

    /**
     * 验证数据.
     *
     * @param array        $data     数据
     * @param string|array $validate 验证器名或者验证规则数组
     * @param array        $message  提示信息
     * @param bool         $batch    是否批量验证
     * @param mixed        $callback 回调方法（闭包）
     *
     * @throws ValidateException
     *
     * @return array|string|true
     */
    protected function validate($data, $validate, $message = [], $batch = false, $callback = null)
    {
        if (is_array($validate)) {
            $v = Loader::validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                list($validate, $scene) = explode('.', $validate);
            }
            $v = Loader::validate('\\plugins\\'.cmf_parse_name($this->plugin->getName()).'\\validate\\'.$validate.'Validate');
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        if (is_array($message)) {
            $v->message($message);
        }

        if ($callback && is_callable($callback)) {
            call_user_func_array($callback, [$v, &$data]);
        }

        if (!$v->check($data)) {
            if ($this->failException) {
                throw new ValidateException($v->getError());
            }

            return $v->getError();
        }

        return true;
    }
}
