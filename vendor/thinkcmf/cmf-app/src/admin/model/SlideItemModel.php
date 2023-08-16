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

class SlideItemModel extends Model
{
    /**
     * 模型名称
     * @var string
     */
    protected $name = 'slide_item';

    protected $type = [
        'more'        => 'array'
    ];

    /**
     * image 自动转化
     * @param $value
     * @return array
     */
    public function getImageUrlAttr($value,$data)
    {
        return cmf_get_image_url($data['image']);
    }
}
