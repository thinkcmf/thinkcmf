<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------
namespace app\user\validate;

use think\Validate;

class FavoriteValidate extends Validate
{
    protected $rule = [
        'id'    => 'require',
        'title' => 'require|checkTitle',
        'table' => 'require',
        'url'   => 'require|checkUrl',
    ];
    protected $message = [
        'id.require'    => '收藏内容ID不能为空!',
        'title.require' => '收藏内容标题不能为空!',
        'table.require' => '收藏内容所在表不能为空!',
        'url.require'   => '收藏内容链接不能为空!',
        'url.checkUrl'  => '收藏内容链接格式不正确!'
    ];

    protected $scene = [
    ];

    // 验证url 格式
    protected function checkUrl($value, $rule, $data)
    {
        $url = json_decode(base64_decode($value), true);

        if (!empty($url['action'])) {
            return true;
        }
        return '收藏内容链接格式不正确!';
    }

    // 验证url 格式
    protected function checkTitle($value, $rule, $data)
    {
        if (base64_decode($value)!==false) {
            return true;
        }
        return '收藏内容标题格式不正确!';
    }
}