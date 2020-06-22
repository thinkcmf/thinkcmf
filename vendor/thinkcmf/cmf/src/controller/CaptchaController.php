<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------

namespace cmf\controller;

use think\captcha\Captcha;
use think\facade\Config;
use think\Request;

class CaptchaController
{
    /**
     * new_captcha?height=50&width=200&font_size=25&length=4&bg=243,251,254&id=1
     * @param Request $request
     * @return \think\Response
     */
    public function index(Request $request)
    {
        $config = [
            // 验证码字体大小(px)
            'fontSize' => 25,
            // 验证码图片高度
            'imageH'   => 38,
            // 验证码图片宽度
            'imageW'   => 120,
            // 验证码位数
            'length'   => 4,
            // 背景颜色
            'bg'       => [255, 255, 255],
        ];

        $fontSize = $request->param('font_size', 25, 'intval');
        if ($fontSize > 8 && $fontSize < 100) {
            $config['fontSize'] = $fontSize;
        }


        $imageH = $request->param('height', '');
        if ($imageH != '' && $imageH < 100) {
            $config['imageH'] = intval($imageH);
        }

        $imageW = $request->param('width', '');
        if ($imageW != '' && $imageW < 200) {
            $config['imageW'] = intval($imageW);
        }

        $length = $request->param('length', 4, 'intval');
        if ($length > 2 && $length <= 100) {
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
            $id                   = '';
            $config['captcha_id'] = $id;
        }

        $response = hook_one('captcha_image', $config);
        if (empty($response)) {
            $defaultCaptchaConfig = (array)Config::pull('captcha');
            if ($defaultCaptchaConfig && is_array($defaultCaptchaConfig)) {
                $config = array_merge($defaultCaptchaConfig, $config);
            }
            $captcha  = new Captcha($config);
            $response = $captcha->entry($id);
        }
        @ob_clean();// 清除输出缓存
        return $response;
    }
}
