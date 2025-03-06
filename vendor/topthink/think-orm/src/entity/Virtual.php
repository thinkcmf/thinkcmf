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
 * Class Virtual.
 * 虚拟实体模型
 */
abstract class Virtual extends Entity
{
    /**
     * 设置为虚拟模型.
     *
     * @return bool
     */
    public function isVirtual(): bool
    {
        return true;
    }
}
