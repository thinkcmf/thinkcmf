<?php

namespace cmf\lib;
// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi.cn@gmail.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
// | ThinkOauth.class.php 2013-02-25
// +----------------------------------------------------------------------
// | 老猫 <catman@thinkcmf.com> 规范代码和用法
// +----------------------------------------------------------------------

abstract class Oauth2
{
    /**
     * oauth版本
     * @var string
     */
    protected $version = '2.0';

    /**
     * 申请应用时分配的app_key
     * @var string
     */
    protected $appKey = '';

    /**
     * 申请应用时分配的 app_secret
     * @var string
     */
    protected $appSecret = '';

    /**
     * 授权类型 response_type 目前只能为code
     * @var string
     */
    protected $responseType = 'code';

    /**
     * grant_type 目前只能为 authorization_code
     * @var string
     */
    protected $grantType = 'authorization_code';

    /**
     * 回调页面URL  可以通过配置文件配置
     * @var string
     */
    protected $callback = '';

    /**
     * 获取request_code的额外参数 URL查询字符串格式
     * @var string
     */
    protected $authorize = '';

    /**
     * 获取request_code请求的URL
     * @var string
     */
    protected $getRequestCodeURL = '';

    /**
     * 获取access_token请求的URL
     * @var string
     */
    protected $getAccessTokenURL = '';

    /**
     * API根路径
     * @var string
     */
    protected $apiBase = '';

    /**
     * 授权后获取到的TOKEN信息
     * @var array
     */
    protected $token = null;

    /**
     * 初始化配置
     */
    private function config()
    {
//        $config = C("THINK_SDK_{$this->Type}");
//        if (!empty($config['AUTHORIZE']))
//            $this->authorize = $config['AUTHORIZE'];
//        if (!empty($config['CALLBACK']))
//            $this->callback = $config['CALLBACK'];
//        else
//            throw new Exception('请配置回调页面地址');
    }

    /**
     * @param string $appKey
     */
    public function setAppKey(string $appKey)
    {
        $this->appKey = $appKey;
    }

    /**
     * @param string $callback
     */
    public function setCallback(string $callback)
    {
        $this->callback = $callback;
    }


    /**
     * @param string $appSecret
     */
    public function setAppSecret(string $appSecret)
    {
        $this->appSecret = $appSecret;
    }


    /**
     * 请求code
     */
    public function getRequestCodeURL()
    {
        $this->config();
        //Oauth 标准参数
        $params = [
            'client_id'     => $this->appKey,
            'redirect_uri'  => $this->callback,
            'response_type' => $this->responseType,
        ];

        //获取额外参数
        if ($this->authorize) {
            parse_str($this->authorize, $_param);
            if (is_array($_param)) {
                $params = array_merge($params, $_param);
            } else {
                throw new \Exception('AUTHORIZE配置不正确！');
            }
        }
        return $this->getRequestCodeURL . '?' . http_build_query($params);
    }

    /**
     * 获取access_token
     * @param string $code 上一步请求到的code
     * @param null $extend
     * @return array
     */
    public function getAccessToken($code, $extend = null)
    {
        $this->config();
        $params = [
            'client_id'     => $this->appKey,
            'client_secret' => $this->appSecret,
            'grant_type'    => $this->grantType,
            'code'          => $code,
            'redirect_uri'  => $this->callback,
        ];

        $data        = $this->http($this->getAccessTokenURL, $params, 'POST');
        $this->token = $this->parseToken($data, $extend);
        return $this->token;
    }

    /**
     * 合并默认参数和额外参数
     * @param array $params 默认参数
     * @param array /string $param 额外参数
     * @return array:
     */
    protected function param($params, $param)
    {
        if (is_string($param))
            parse_str($param, $param);
        return array_merge($params, $param);
    }

    /**
     * 获取指定API请求的URL
     * @param  string $api API名称
     * @param  string $fix api后缀
     * @return string      请求的完整URL
     */
    protected function url($api, $fix = '')
    {
        return $this->apiBase . $api . $fix;
    }

    /**
     * /**
     * 发送HTTP请求方法，目前只支持CURL发送请求
     * @param string $url 请求URL
     * @param array $params 请求参数
     * @param string $method 请求方法GET/POST
     * @param array $header
     * @param bool $multi
     * @return array  $data   响应数据
     * @throws \Exception
     */
    protected function http($url, $params, $method = 'GET', $header = [], $multi = false)
    {
        $opts = [
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => $header
        ];

        /* 根据请求类型设置特定参数 */
        switch (strtoupper($method)) {
            case 'GET':
                $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
                break;
            case 'POST':
                //判断是否传输文件
                $params                   = $multi ? $params : http_build_query($params);
                $opts[CURLOPT_URL]        = $url;
                $opts[CURLOPT_POST]       = 1;
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
            default:
                throw new \Exception('不支持的请求方式！');
        }

        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data  = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($error) throw new \Exception('请求发生错误：' . $error);
        return $data;
    }

    /**
     * 组装接口调用参数 并调用接口
     * @param $api
     * @param string $param
     * @param string $method
     * @param bool $multi
     * @return mixed
     */
    abstract protected function call($api, $param = '', $method = 'GET', $multi = false);

    /**
     * 解析access_token方法请求后的返回值
     * @param $result
     * @param $extend
     */
    abstract protected function parseToken($result, $extend);

    /**
     * 获取当前授权用户的SNS标识
     */
    abstract public function openid();
}