<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
return array (
	'account_sid' => array (// 在后台插件配置表单中的键名 ,会是config[text]
		'title' => 'ACCOUNT SID', // 表单的label标题
		'type' => 'text',// 表单的类型：text,password,textarea,checkbox,radio,select等
		'value' => '',// 表单的默认值
		'tip' => '主帐号,对应开发者官网主账号下的ACCOUNT SID' //表单的帮助提示
	),
    'auth_token' => array (// 在后台插件配置表单中的键名 ,会是config[text]
        'title' => 'AUTH TOKEN', // 表单的label标题
        'type' => 'text',// 表单的类型：text,password,textarea,checkbox,radio,select等
        'value' => '',// 表单的默认值
        'tip' => '主帐号令牌,对应官网开发者主账号下的 AUTH TOKEN' //表单的帮助提示
    ),
    'app_id' => array (// 在后台插件配置表单中的键名 ,会是config[text]
        'title' => 'APP ID', // 表单的label标题
        'type' => 'text',// 表单的类型：text,password,textarea,checkbox,radio,select等
        'value' => '',// 表单的默认值
        'tip' => '应用Id，在官网应用列表中点击应用，对应应用详情中的APP ID' //表单的帮助提示
    ),
    'template_id' => array (// 在后台插件配置表单中的键名 ,会是config[text]
        'title' => '模板ID', // 表单的label标题
        'type' => 'text',// 表单的类型：text,password,textarea,checkbox,radio,select等
        'value' => '',// 表单的默认值
        'tip' => '模板Id,测试应用和未上线应用使用测试模板请填写1，正式应用上线后填写已申请审核通过的模板ID' //表单的帮助提示
    ),
    'expire_minute' => array (// 在后台插件配置表单中的键名 ,会是config[text]
        'title' => '有效期', // 表单的label标题
        'type' => 'text',// 表单的类型：text,password,textarea,checkbox,radio,select等
        'value' => '30',// 表单的默认值
        'tip' => '短信验证码过期时间，单位分钟' //表单的帮助提示
    ),
);
					