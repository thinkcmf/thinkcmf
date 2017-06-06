<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------

namespace cmf\controller;

use think\captcha\Captcha;
use think\Request;

class CaptchaController
{
    /**
     * captcha/new?height=50&width=200&font_size=25&length=4&bg=243,251,254&id=1
     * @param Request $request
     * @return \think\Response
     */
    public function index(Request $request)
    {
        $config = [
            // 验证码字体大小(px)
            'fontSize' => 25,
            // 验证码图片高度
            'imageH'   => 0,
            // 验证码图片宽度
            'imageW'   => 0,
            // 验证码位数
            'length'   => 4,
            // 背景颜色
            'bg'       => [243, 251, 254],
        ];

        $fontSize = $request->param('font_size', 25, 'intval');
        if ($fontSize > 8) {
            $config['fontSize'] = $fontSize;
        }

        $imageH = $request->param('height', '');
        if ($imageH != '') {
            $config['imageH'] = intval($imageH);
        }

        $imageW = $request->param('width', '');
        if ($imageW != '') {
            $config['imageW'] = intval($imageW);
        }

        $length = $request->param('length', 4, 'intval');
        if ($length > 2) {
            $config['length'] = $length;
        }

        $bg = $request->param('bg', '');

        if (!empty($bg)) {
            $bg = explode(',', $bg);
            array_walk($bg, 'intval');
            if (count($bg) > 2 && $bg[0] < 256 && $bg[1] < 256 && $bg[2] < 256) {
                $config['bg'] = $bg;
            }
        }

        $id = $request->param('id', 0, 'intval');
        if ($id > 5 || empty($id)) {
            $id = '';
        }

        $captcha = new Captcha($config);
        return $captcha->entry($id);
    }
}