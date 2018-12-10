<?php
/**
 * Created by PhpStorm.
 * User: xavier
 * Date: 2018/8/15
 * Time: 下午2:14
 * 秒 分 时 日 月 星期几
 * crontab 格式 * *  *  *  * *    => "类"
 * *中间一个空格
 * 系统定时任务需要在swoole.php中开启
 * 自定义定时器不受其影响
 */

return [
    '*/5 * * * * *' => '\\app\\lib\\Timer',
];
