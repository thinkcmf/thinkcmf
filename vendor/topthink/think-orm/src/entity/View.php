<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2025 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\entity;

use think\Entity;

/**
 * Class View.
 * 视图实体模型
 */
abstract class View extends Entity
{
    /**
     * 设置为视图模型.
     *
     * @return bool
     */
    public function isView(): bool
    {
        return true;
    }

    /**
     * 指定视图查询.
     *
     * @param string  $model  实体模型类名
     * @param string|array|bool $field 查询字段
     * @param string       $on    JOIN条件
     * @param string       $type  JOIN类型
     *
     * @return $this
     */
    public function view(string $model, $field, ?string $on = null, string $joinType = 'INNER')
    {
        $this->model()->view($model::getTable() . ' ' . class_basename($model), $field, $on, $joinType);
        return $this;
    }
}
