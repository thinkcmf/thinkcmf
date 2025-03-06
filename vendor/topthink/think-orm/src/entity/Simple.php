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
 * Class Simple.
 * 简单实体模型 仅限单表操作 不绑定模型
 */
abstract class Simple extends Entity
{
    /**
     * 设置为单表模型.
     *
     * @return bool
     */
    public function isSimple(): bool
    {
        return true;
    }
}
