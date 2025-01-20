<?php

namespace cmf\traits;

trait GetHeaderToken
{
    /**
     * 获取Token
     *
     * 兼容三种传输token
     * 1 Authorization: Bearer token
     * 2 Authorization: token
     * 3 XX-Token: token
     * @return string
     */
    protected function getHeaderToken()
    {
        // 优先 Authorization
        $token = $this->request->header('Authorization', '');
        if (substr($token, 0, 7) === 'Bearer ') {// 如果是标准的 带Bearer 的 截取一下
            $token = substr($token, 7);
        }
        if (empty($token)) {
            $token = $this->request->header('XX-Token');
        }
        return $token;
    }
}