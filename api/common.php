<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// | Date: 2019/01/08
// | Time:下午 03:29
// +----------------------------------------------------------------------
/**
 * 转换+-为desc和asc
 * @param $order array 转换对象
 * @return array
 */
function order_shift($order)
{
    $orderArr = [];
    foreach ($order as $key => $value) {
        $upDwn      = substr($value, 0, 1);
        $orderType  = $upDwn == '-' ? 'desc' : 'asc';
        $orderField = substr($value, 1);
        if (!empty($whiteParams)) {
            if (in_array($orderField, $whiteParams)) {
                $orderArr[$orderField] = $orderType;
            }
        } else {
            $orderArr[$orderField] = $orderType;
        }
    }
    return $orderArr;
}

/**
 * 模型检查
 * @param $relationFilter array 检查的字段
 * @param $relations string 被检查的字段
 * @return array|bool
 */
function allowed_relations($relationFilter,$relations)
{
    if (is_string($relations)) {
        $relations = explode(',', $relations);
    }
    if (!is_array($relations)) {
        return false;
    }
    return array_intersect($relationFilter, $relations);
}
/**
 * 字符串转数组
 * @param string $string 字符串
 * @return array
 */
function str_to_arr($string)
{
    $result = is_string($string) ? explode(',', $string) : $string;
    return $result;
}