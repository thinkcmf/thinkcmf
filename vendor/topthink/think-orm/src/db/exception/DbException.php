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

use think\Exception;

/**
 * Database相关异常处理类
 */
class DbException extends Exception
{
    /**
     * DbException constructor.
     * @access public
     * @param  string    $message
     * @param  array     $config
     * @param  string    $sql
     * @param  int       $code
     */
    public function __construct(string $message, array $config = [], string $sql = '', int $code = 10500)
    {
        $this->message = $message;
        $this->code    = $code;

        $this->setData('Database Status', [
            'Error Code'    => $code,
            'Error Message' => $message,
            'Error SQL'     => $sql,
        ]);

        unset($config['username'], $config['password']);
        $this->setData('Database Config', $config);
    }
}
