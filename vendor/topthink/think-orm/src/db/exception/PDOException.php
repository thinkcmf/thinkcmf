<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://zjzit.cn>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\db\exception;

/**
 * PDO异常处理类
 * 重新封装了系统的\PDOException类
 */
class PDOException extends DbException
{
    /**
     * PDOException constructor.
     * @access public
     * @param  \PDOException $exception
     * @param  array         $config
     * @param  string        $sql
     * @param  int           $code
     */
    public function __construct(\PDOException $exception, array $config = [], string $sql = '', int $code = 10501)
    {
        $error   = $exception->errorInfo;
        $message = $exception->getMessage();

        if (!empty($error)) {
            $this->setData('PDO Error Info', [
                'SQLSTATE'             => $error[0],
                'Driver Error Code'    => isset($error[1]) ? $error[1] : 0,
                'Driver Error Message' => isset($error[2]) ? $error[2] : '',
            ]);
        }

        parent::__construct($message, $config, $sql, $code);
    }
}
