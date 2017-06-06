<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace app\admin\annotation;

use mindplay\annotations\Annotation;

/**
 * Specifies validation of a string, requiring a minimum and/or maximum length.
 *
 * @usage('method'=>true, 'inherited'=>true, 'multiple'=>false)
 */
class AdminMenuAnnotation extends Annotation
{
    public $remark = '';

    public $icon = '';

    public $name = '';

    public $param = '';

    public $parent = '';

    public $display = false;

    public $order = 10000;

    public $hasView = true;

    /**
     * Initialize the annotation.
     */
    public function initAnnotation(array $properties)
    {
        parent::initAnnotation($properties);
    }
}
