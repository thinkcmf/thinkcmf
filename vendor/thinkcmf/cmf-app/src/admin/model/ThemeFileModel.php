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

class ThemeFileModel extends Model
{
    /**
     * 模型名称
     * @var string
     */
    protected $name = 'theme_file';

    protected $type = [
        'more'        => 'array',
        'config_more' => 'array',
        'draft_more'  => 'array'
    ];

    public function fillBlockWidgetValue($blockName, $widgetId)
    {
        $oldMore = $this['more'];
        $widget  = [];
        if (isset($oldMore['widgets_blocks'][$blockName]['widgets'][$widgetId])) {
            $widgetWithValue = $oldMore['widgets_blocks'][$blockName]['widgets'][$widgetId];
            $theme           = $this['theme'];
            $widgetManifest  = file_get_contents(WEB_ROOT . "themes/$theme/public/widgets/{$widgetWithValue['name']}/manifest.json");
            $widget          = json_decode($widgetManifest, true);

            foreach ($widgetWithValue as $key => $value) {
                if ($key == 'vars') {
                    foreach ($value as $varName => $varValue) {
                        if (isset($widget['vars'][$varName])) {
                            $widget['vars'][$varName]['value'] = $varValue;
                        }
                    }
                } else {
                    $widget[$key] = $value;
                }
            }

        }

        return $widget;
    }
}
