<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
return [
    'accessKey'                 => [// 在后台插件配置表单中的键名 ,会是config[text]
        'title'   => 'AccessKey', // 表单的label标题
        'type'    => 'text',// 表单的类型：text,password,textarea,checkbox,radio,select等
        'value'   => '',// 表单的默认值
        "rule"    => [
            "require" => true
        ],
        "message" => [
            "require" => 'AccessKey不能为空'
        ],
        'tip'     => '<a href="https://portal.qiniu.com/signup?code=3lfihpz361o42" target="_blank">马上获取</a>,充值使用ThinkCMF七牛专属优惠码<a href="http://www.thinkcmf.com/qiniu/promotion_code.html" target="_blank">507670e8</a>有更多优惠,<a href="http://www.thinkcmf.com/faq.html?url=https://www.kancloud.cn/thinkcmf/faq/507454" target="_blank">查看帮助手册</a>' //表单的帮助提示
    ],
    'secretKey'                 => [// 在后台插件配置表单中的键名 ,会是config[password]
        'title'   => 'SecretKey',
        'type'    => 'text',
        'value'   => '',
        "rule"    => [
            "require" => true
        ],
        "message" => [
            "require" => 'SecretKey不能为空'
        ],
        'tip'     => '<a href="https://portal.qiniu.com/signup?code=3lfihpz361o42" target="_blank">马上获取</a>, <a href="http://www.thinkcmf.com/faq.html?url=https://www.kancloud.cn/thinkcmf/faq/507454" target="_blank">查看帮助手册</a>'
    ],
    'protocol'                  => [// 在后台插件配置表单中的键名 ,会是config[select]
        'title'   => '域名协议',
        'type'    => 'select',
        'options' => [//select 和radio,checkbox的子选项
            'http'  => 'http',// 值=>显示
            'https' => 'https',
        ],
        'value'   => 'http',
        "rule"    => [
            "require" => true
        ],
        "message" => [
            "require" => '域名协议不能为空'
        ],
        'tip'     => ''
    ],
    'domain'                    => [
        'title'   => '空间域名',
        'type'    => 'text',
        'value'   => '',
        "rule"    => [
            "require" => true
        ],
        "message" => [
            "require" => '空间域名不能为空'
        ],
        'tip'     => ''
    ],
    'bucket'                    => [
        'title'   => '空间名称',
        'type'    => 'text',
        'value'   => '',
        "rule"    => [
            "require" => true
        ],
        "message" => [
            "require" => '空间名称不能为空'
        ],
        'tip'     => ''
    ],
    'zone'                      => [// 在后台插件配置表单中的键名 ,会是config[select]
        'title'   => '存储区域',
        'type'    => 'select',
        'options' => [//select 和radio,checkbox的子选项
            'z0'  => '华东',// 值=>显示
            'z1'  => '华北',
            'z2'  => '华南',
            'na0' => '北美',
        ],
        'value'   => 'http',
        "rule"    => [
            "require" => true
        ],
        "message" => [
            "require" => '存储区域不能为空'
        ],
        'tip'     => ''
    ],
    'style_separator'           => [
        'title'   => '样式分隔符',
        'type'    => 'text',
        'value'   => '!',
        "rule"    => [
            "require" => true
        ],
        "message" => [
            "require" => '样式分隔符不能为空'
        ],
        'tip'     => ''
    ],
    'styles_watermark'          => [
        'title'   => '样式-水印',
        'type'    => 'text',
        'value'   => 'watermark',
        "rule"    => [
            "require" => true
        ],
        "message" => [
            "require" => '样式-水印不能为空'
        ],
        'tip'     => '请到七牛存储空间->图片样式：添加此样式名称，并进行相应设置 处理接口:<br>imageMogr2/auto-orient/thumbnail/1080x1080>/blur/1x0/quality/75|watermark/2/text/VGhpbmtDTUY=/font/5b6u6L2v6ZuF6buR/fontsize/500/fill/I0ZGRkZGRg==/dissolve/100/gravity/SouthEast/dx/10/dy/10'
    ],
    'styles_avatar'             => [
        'title'   => '样式-头像',
        'type'    => 'text',
        'value'   => 'avatar',
        "rule"    => [
            "require" => true
        ],
        "message" => [
            "require" => '样式-头像不能为空'
        ],
        'tip'     => '请到七牛存储空间->图片样式：添加此样式名称，并进行相应设置 处理接口:<br>imageMogr2/auto-orient/thumbnail/!100x100r/gravity/Center/crop/100x100/quality/100/interlace/0'
    ],
    'styles_thumbnail120x120'   => [
        'title'   => '样式-缩略图120x120',
        'type'    => 'text',
        'value'   => 'thumbnail120x120',
        "rule"    => [
            "require" => true
        ],
        "message" => [
            "require" => '样式-缩略图120x120不能为空'
        ],
        'tip'     => '请到七牛存储空间->图片样式：添加此样式名称，并进行相应设置<br>处理接口:<br>imageMogr2/auto-orient/thumbnail/!120x120r/gravity/Center/crop/120x120/quality/100/interlace/0'
    ],
    'styles_thumbnail300x300'   => [
        'title'   => '样式-缩略图300x300',
        'type'    => 'text',
        'value'   => 'thumbnail300x300',
        "rule"    => [
            "require" => true
        ],
        "message" => [
            "require" => '样式-缩略图300x300不能为空'
        ],
        'tip'     => '请到七牛存储空间->图片样式：添加此样式名称，并进行相应设置<br>处理接口:<br>imageMogr2/auto-orient/thumbnail/!300x300r/gravity/Center/crop/300x300/quality/100/interlace/0'
    ],
    'styles_thumbnail640x640'   => [
        'title'   => '样式-缩略图640x640',
        'type'    => 'text',
        'value'   => 'thumbnail640x640',
        "rule"    => [
            "require" => true
        ],
        "message" => [
            "require" => '样式-缩略图640x640不能为空'
        ],
        'tip'     => '请到七牛存储空间->图片样式：添加此样式名称，并进行相应设置<br>处理接口:<br>imageMogr2/auto-orient/thumbnail/!640x640r/gravity/Center/crop/640x640/quality/100/interlace/0'
    ],
    'styles_thumbnail1080x1080' => [
        'title'   => '样式-缩略图1080x1080',
        'type'    => 'text',
        'value'   => 'thumbnail1080x1080',
        "rule"    => [
            "require" => true
        ],
        "message" => [
            "require" => '样式-缩略图1080x1080不能为空'
        ],
        'tip'     => '请到七牛存储空间->图片样式：添加此样式名称，并进行相应设置<br>处理接口:<br>imageMogr2/auto-orient/thumbnail/!1080x1080r/gravity/Center/crop/1080x1080/quality/100/interlace/0'
    ],
];
					