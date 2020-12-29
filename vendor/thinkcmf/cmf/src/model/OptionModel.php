<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>,老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace cmf\model;

use think\Model;

class OptionModel extends Model
{
    /**
     * 模型名称
     * @var string
     */
    protected $name = 'option';

    /**
     * 数据表字段类型
     * @var array
     */
    protected $type = [
        'option_value' => 'array',
    ];


}
