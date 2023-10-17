<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\model;

use think\Model;

class ThemeFileI18nModel extends Model
{
    /**
     * 模型名称
     * @var string
     */
    protected $name = 'theme_file_i18n';

    protected $type = [
        'more'       => 'array',
        'draft_more' => 'array',
    ];


}
