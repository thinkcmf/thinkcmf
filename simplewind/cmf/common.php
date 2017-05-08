<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------

use think\Config;
use think\Db;
use think\Cache;
use think\Url;
use dir\Dir;
use think\Route;
use think\Loader;
use think\Request;

// 应用公共文件

//设置插件入口路由
Route::get('plugin/[:_plugin]/[:_controller]/[:_action]', "\\cmf\\controller\\PluginController@index");
Route::get('captcha/new', "\\cmf\\controller\\CaptchaController@index");

/**
 * 获取当前登录的管事员id
 * @return int
 */
function cmf_get_current_admin_id()
{
    return session('ADMIN_ID');
}

/**
 * 判断前台用户是否登录
 * @return boolean
 */
function cmf_is_user_login()
{
    $sessionUser = session('user');
    return !empty($sessionUser);
}

/**
 * 获取当前登录的前台用户的信息，未登录时，返回false
 * @return array|boolean
 */
function cmf_get_current_user()
{
    $sessionUser = session('user');
    if (!empty($sessionUser)) {
        return $sessionUser;
    } else {
        return false;
    }
}

/**
 * 更新当前登录前台用户的信息
 * @param array $user 前台用户的信息
 */
function cmf_update_current_user($user)
{
    session('user', $user);
}

/**
 * 获取当前登录前台用户id
 * @return int
 */
function cmf_get_current_user_id()
{
    $sessionUserId = session('user.id');
    if (!empty($sessionUserId)) {
        return $sessionUserId;
    } else {
        return 0;
    }
}

/**
 * 返回带协议的域名
 */
function cmf_get_domain()
{
    $request = Request::instance();
    return $request->domain();
}

/**
 * 获取程序web根目录
 * @return string web根目录
 */
function cmf_get_root()
{
    $request = Request::instance();
    $root    = $request->root();
    $root    = str_replace('/index.php', '', $root);
    return $root;
}

/**
 * @TODO 增加主题切换时获取当然主题
 * 获取当前主题名
 * @return string
 */
function cmf_get_current_theme()
{
//    $tmpl_path = C("SP_TMPL_PATH");
//    $theme     = C('SP_DEFAULT_THEME');
//    if (C('TMPL_DETECT_THEME')) {
//        $t = C('VAR_TEMPLATE');
//        if (isset($_GET[$t])) {
//            $theme = $_GET[$t];
//        } elseif (cookie('think_template')) {
//            $theme = cookie('think_template');
//        }
//        if (!file_exists($tmpl_path . "/" . $theme)) {
//            $theme = C('SP_DEFAULT_THEME');
//        }
//        cookie('think_template', $theme, 864000);
//    }

    $theme = config('cmf_default_theme');

    return $theme;
}

/**
 * 获取前台模板根目录
 * @param string $theme
 * @return string 前台模板根目录
 */
function cmf_get_theme_path($theme = null)
{
    $themePath = config('cmf_theme_path');
    if ($theme === null) {
        // 获取当前主题名称
        $theme = cmf_get_current_theme();
    }

    return './' . $themePath . $theme;
}

/**
 * @TODO
 * 获取用户头像相对网站根目录的地址
 * @param $avatar 用户头像,相对于 upload 目录
 * @return string
 */
function cmf_get_user_avatar_url($avatar)
{

    //TODO FIX
    if (!empty($avatar)) {
        if (strpos($avatar, "http") === 0) {
            return $avatar;
        } else {
            if (strpos($avatar, 'avatar/') === false) {
                $avatar = 'avatar/' . $avatar;
            }

            return cmf_get_asset_url($avatar);

            //TODO 七牛处理
//            $url = cmf_get_asset_url($avatar, false);
//            if (C('FILE_UPLOAD_TYPE') == 'Qiniu') {
//                $storage_setting = cmf_get_cmf_settings('storage');
//                $qiniu_setting   = $storage_setting['Qiniu']['setting'];
//                $filePath        = $qiniu_setting['protocol'] . '://' . $storage_setting['Qiniu']['domain'] . "/" . $avatar;
//                if ($qiniu_setting['enable_picture_protect']) {
//                    $url = $url . $qiniu_setting['style_separator'] . $qiniu_setting['styles']['avatar'];
//                }
//            }
//
//            return $url;
        }

    } else {
        return $avatar;
    }

}

/**
 * CMF密码加密方法
 * @param string $pw 要加密的字符串
 * @return string
 */
function cmf_password($pw, $authCode = '')
{
    if (empty($authCode)) {
        $authCode = Config::get('database.authcode');
    }
    $result = "###" . md5(md5($authCode . $pw));
    return $result;
}

/**
 * CMF密码加密方法 (X2.0.0以前的方法)
 * @param string $pw 要加密的字符串
 * @return string
 */
function cmf_password_old($pw)
{
    $decor = md5(Config::get('database.prefix'));
    $mi    = md5($pw);
    return substr($decor, 0, 12) . $mi . substr($decor, -4, 4);
}

/**
 * CMF密码比较方法,所有涉及密码比较的地方都用这个方法
 * @param string $password 要比较的密码
 * @param string $password_in_db 数据库保存的已经加密过的密码
 * @return boolean 密码相同，返回true
 */
function cmf_compare_password($password, $password_in_db)
{
    if (strpos($password_in_db, "###") === 0) {
        return cmf_password($password) == $password_in_db;
    } else {
        return cmf_password_old($password) == $password_in_db;
    }
}

/**
 * 文件日志
 * @param $content 要写入的内容
 * @param string $file 日志文件,在web 入口目录
 */
function cmf_log($content, $file = "log.txt")
{
    file_put_contents($file, $content, FILE_APPEND);
}

/**
 * 随机字符串生成
 * @param int $len 生成的字符串长度
 * @return string
 */
function cmf_random_string($len = 6)
{
    $chars    = [
        "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
        "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
        "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
        "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
        "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
        "3", "4", "5", "6", "7", "8", "9"
    ];
    $charsLen = count($chars) - 1;
    shuffle($chars);    // 将数组打乱
    $output = "";
    for ($i = 0; $i < $len; $i++) {
        $output .= $chars[mt_rand(0, $charsLen)];
    }
    return $output;
}

/**
 * 清空系统缓存
 */
function cmf_clear_cache()
{
    $dirs     = [];
    $rootDirs = cmf_scan_dir(RUNTIME_PATH . "*");
    //$noNeedClear=array(".","..","Data");
    $noNeedClear = [".", ".."];
    $rootDirs    = array_diff($rootDirs, $noNeedClear);
    foreach ($rootDirs as $dir) {

        if ($dir != "." && $dir != "..") {
            $dir = RUNTIME_PATH . $dir;
            if (is_dir($dir)) {
                //array_push ( $dirs, $dir );
                $tmpRootDirs = cmf_scan_dir($dir . "/*");
                foreach ($tmpRootDirs as $tDir) {
                    if ($tDir != "." && $tDir != "..") {
                        $tDir = $dir . '/' . $tDir;
                        if (is_dir($tDir)) {
                            array_push($dirs, $tDir);
                        } else {
                            @unlink($tDir);
                        }
                    }
                }
            } else {
                @unlink($dir);
            }
        }
    }
    $dirTool = new Dir("");
    foreach ($dirs as $dir) {
        $dirTool->delDir($dir);
    }
}

/**
 * 保存数组变量到php文件
 * @param string $path 保存路径
 * @param mixed $var 要保存的变量
 * @return boolean 保存成功返回true,否则false
 */
function cmf_save_var($path, $var)
{
    $result = file_put_contents($path, "<?php\treturn " . var_export($var, true) . ";?>");
    return $result;
}

/**
 * 更新系统配置文件
 * @param array $data <br>如：["cmf_default_theme"=>'simpleboot3'];
 * @return boolean
 */
function cmf_set_dynamic_config($data)
{

    if (!is_array($data)) {
        return false;
    }

    $configFile = CMF_ROOT . "data/conf/config.php";
    if (file_exists($configFile)) {
        $configs = include $configFile;
    } else {
        $configs = [];
    }

    $configs = array_merge($configs, $data);
    $result  = file_put_contents($configFile, "<?php\treturn " . var_export($configs, true) . ";");

    cmf_clear_cache();
    return $result;
}

/**
 * 转化格式化的字符串为数组
 * @param string $tag 要转化的字符串,格式如:"id:2;cid:1;order:post_date desc;"
 * @return array 转化后字符串<pre>
 * array(
 *  'id'=>'2',
 *  'cid'=>'1',
 *  'order'=>'post_date desc'
 * )
 */
function cmf_param_lable($tag = '')
{
    $param = [];
    $array = explode(';', $tag);
    foreach ($array as $v) {
        $v = trim($v);
        if (!empty($v)) {
            list($key, $val) = explode(':', $v);
            $param[trim($key)] = trim($val);
        }
    }
    return $param;
}

/**
 * 获取后台管理设置的网站信息，此类信息一般用于前台
 */
function cmf_get_site_info()
{
    $siteInfo = cmf_get_option('site_info');

    if (isset($siteInfo['site_analytics'])) {
        $siteInfo['site_analytics'] = htmlspecialchars_decode($siteInfo['site_analytics']);
    }

    return $siteInfo;
}

/**
 * 获取CMF系统的设置，此类设置用于全局
 * @return array
 */
function cmf_get_cmf_setting()
{
    return cmf_get_option('cmf_setting');
}

/**
 * 更新CMF系统的设置，此类设置用于全局
 * @param array $data
 * @return boolean
 */
function cmf_set_cmf_setting($data)
{
    if (!is_array($data) || empty($data)) {
        return false;
    }

    return cmf_set_option('cmf_setting', $data);
}

/**
 * 设置系统配置，通用
 * @param string $key 配置键值,都小写
 * @param array $data 配置值，数组
 * @return boolean
 */
function cmf_set_option($key, $data)
{
    if (!is_array($data) || empty($data) || !is_string($key) || empty($key)) {
        return false;
    }

    $key        = strtolower($key);
    $option     = [];
    $findOption = Db::name('option')->where('option_name', $key)->find();
    if ($findOption) {
        $oldOptionValue = json_decode($findOption['option_value'], true);
        if (!empty($oldOptionValue)) {
            $data = array_merge($oldOptionValue, $data);
        }

        $option['option_value'] = json_encode($data);

        Db::name('option')->where('option_name', $key)->update($option);
    } else {
        $option['option_name']  = $key;
        $option['option_value'] = json_encode($data);
        Db::name('option')->insert($option);
    }

    //TODO 增加缓存
    return true;
}

/**
 * 获取系统配置，通用
 * @param string $key 配置键值,都小写
 * @return array
 */
function cmf_get_option($key)
{
    if (!is_string($key) || empty($key)) {
        return [];
    }

    //TODO 增加缓存

    if (empty($optionValue)) {
        $optionValue = Db::name('option')->where('option_name', $key)->value('option_value');
        if (!empty($optionValue)) {
            $optionValue = json_decode($optionValue, true);

            return $optionValue;
        }
    }

    return [];
}

/**
 * 获取CMF上传配置
 */
function cmf_get_upload_setting()
{
    $uploadSetting = cmf_get_option('upload_setting');
    if (empty($uploadSetting) || empty($uploadSetting['file_types'])) {
        $uploadSetting = [
            'file_types' => [
                'image' => [
                    'upload_max_filesize' => '10240',//单位KB
                    'extensions'          => 'jpg,jpeg,png,gif,bmp4'
                ],
                'video' => [
                    'upload_max_filesize' => '10240',
                    'extensions'          => 'mp4,avi,wmv,rm,rmvb,mkv'
                ],
                'audio' => [
                    'upload_max_filesize' => '10240',
                    'extensions'          => 'mp3,wma,wav'
                ],
                'file'  => [
                    'upload_max_filesize' => '10240',
                    'extensions'          => 'txt,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar'
                ]
            ],
            'chunk_size' => 512,//单位KB
            'max_files'  => 20 //最大同时上传文件数
        ];
    }

    if (empty($uploadSetting['upload_max_filesize'])) {
        $uploadMaxFileSizeSetting = [];
        foreach ($uploadSetting['file_types'] as $setting) {
            $extensions = explode(',', trim($setting['extensions']));
            if (!empty($extensions)) {
                $uploadMaxFileSize = intval($setting['upload_max_filesize']) * 1024;//转化成B
                foreach ($extensions as $ext) {
                    if (!isset($uploadMaxFileSizeSetting[$ext]) || $uploadMaxFileSize > $uploadMaxFileSizeSetting[$ext] * 1024) {
                        $uploadMaxFileSizeSetting[$ext] = $uploadMaxFileSize;
                    }
                }
            }
        }

        $uploadSetting['upload_max_filesize'] = $uploadMaxFileSizeSetting;
    }

    return $uploadSetting;
}

/**
 * 获取html文本里的img
 * @param string $content
 * @return array
 */
function cmf_get_content_images($content)
{
    import('phpQuery.phpQuery', EXTEND_PATH);
    \phpQuery::newDocumentHTML($content);
    $pq         = pq(null);
    $images     = $pq->find("img");
    $imagesData = [];
    if ($images->length) {
        foreach ($images as $img) {
            $img            = pq($img);
            $image          = [];
            $image['src']   = $img->attr("src");
            $image['title'] = $img->attr("title");
            $image['alt']   = $img->attr("alt");
            array_push($imagesData, $image);
        }
    }
    \phpQuery::$documents = null;
    return $imagesData;
}

/**
 * 去除字符串中的指定字符
 * @param string $str 待处理字符串
 * @param string $chars 需去掉的特殊字符
 * @return string
 */
function cmf_strip_chars($str, $chars = '?<*.>\'\"')
{
    return preg_replace('/[' . $chars . ']/is', '', $str);
}

/**
 * 发送邮件
 * @param string $address
 * @param string $subject
 * @param string $message
 * @return array<br>
 * 返回格式：<br>
 * array(<br>
 *    "error"=>0|1,//0代表出错<br>
 *    "message"=> "出错信息"<br>
 * );
 */
function cmf_send_email($address, $subject, $message)
{
    $smtpSetting = cmf_get_option('smtp_setting');
    $mail        = new \PHPMailer();
    // 设置PHPMailer使用SMTP服务器发送Email
    $mail->IsSMTP();
    $mail->IsHTML(true);
    // 设置邮件的字符编码，若不指定，则为'UTF-8'
    $mail->CharSet = 'UTF-8';
    // 添加收件人地址，可以多次使用来添加多个收件人
    $mail->AddAddress($address);
    // 设置邮件正文
    $mail->Body = $message;
    // 设置邮件头的From字段。
    $mail->From = $smtpSetting['from'];
    // 设置发件人名字
    $mail->FromName = $smtpSetting['from_name'];
    // 设置邮件标题
    $mail->Subject = $subject;
    // 设置SMTP服务器。
    $mail->Host = $smtpSetting['host'];
    //by Rainfer
    // 设置SMTPSecure。
    $Secure           = $smtpSetting['smtp_secure'];
    $mail->SMTPSecure = empty($Secure) ? '' : $Secure;
    // 设置SMTP服务器端口。
    $port       = $smtpSetting['port'];
    $mail->Port = empty($port) ? "25" : $port;
    // 设置为"需要验证"
    $mail->SMTPAuth = true;
    // 设置用户名和密码。
    $mail->Username = $smtpSetting['username'];
    $mail->Password = $smtpSetting['password'];
    // 发送邮件。
    if (!$mail->Send()) {
        $mailError = $mail->ErrorInfo;
        return ["error" => 1, "message" => $mailError];
    } else {
        return ["error" => 0, "message" => "success"];
    }
}

/**
 * TODO 增加七牛及其它云存储处理
 * 转化数据库保存的文件路径，为可以访问的url
 * @param string $file
 * @param mixed $style 样式(七牛)
 * @return string
 */
function cmf_get_asset_url($file, $style = '')
{
    if (strpos($file, "http") === 0) {
        return $file;
    } else if (strpos($file, "/") === 0) {

        return $file;
    } else {
        return cmf_get_root() . '/upload/' . $file;
        //TODO 七牛处理
//        $filePath = C("TMPL_PARSE_STRING.__UPLOAD__") . $file;
//        if (C('FILE_UPLOAD_TYPE') == 'Local') {
//            if (strpos($filePath, "http") !== 0) {
//                $filePath = cmf_get_host() . $filePath;
//            }
//        }
//
//        if (C('FILE_UPLOAD_TYPE') == 'Qiniu') {
//            $storage_setting = cmf_get_cmf_settings('storage');
//            $qiniu_setting   = $storage_setting['Qiniu']['setting'];
//            $filePath        = $qiniu_setting['protocol'] . '://' . $storage_setting['Qiniu']['domain'] . "/" . $file . $style;
//        }

//        return $filePath;

    }
}

/**
 * @TODO 增加七牛及其它云存储处理
 * 转化数据库保存图片的文件路径，为可以访问的url
 * @param string $file
 * @param mixed $style 样式(七牛)
 * @return string
 */
function cmf_get_image_url($file, $style = '')
{
    if (strpos($file, "http") === 0) {
        return $file;
    } else if (strpos($file, "/") === 0) {
        return $file;
    } else {

        return cmf_get_root() . '/upload/' . $file;
//        $filePath = C("TMPL_PARSE_STRING.__UPLOAD__") . $file;
//        if (C('FILE_UPLOAD_TYPE') == 'Local') {
//            if (strpos($filePath, "http") !== 0) {
//                $filePath = cmf_get_host() . $filePath;
//            }
//        }
//
//        if (C('FILE_UPLOAD_TYPE') == 'Qiniu') {
//            $storage_setting = cmf_get_cmf_settings('storage');
//            $qiniu_setting   = $storage_setting['Qiniu']['setting'];
//            $filePath        = $qiniu_setting['protocol'] . '://' . $storage_setting['Qiniu']['domain'] . "/" . $file . $style;
//        }

//        return $filePath;

    }
}

/**
 * TODO qiniu 的可能有问题，没有测试过，如果你们测试好了，可以把todo删除
 * 获取图片预览链接
 * @param string $file 文件路径，相对于upload
 * @param string $style 图片样式，只有七牛可以用
 * @return string
 */
function cmf_get_image_preview_url($file, $style = 'watermark')
{
    if (config('FILE_UPLOAD_TYPE') == 'Qiniu') {
        $storage_setting = cmf_get_cmf_settings('storage');
        $qiniu_setting   = $storage_setting['Qiniu']['setting'];
        $filePath        = $qiniu_setting['protocol'] . '://' . $storage_setting['Qiniu']['domain'] . "/" . $file;
        $url             = cmf_get_asset_url($file, false);
        if ($qiniu_setting['enable_picture_protect']) {
            $url = $url . $qiniu_setting['style_separator'] . $qiniu_setting['styles'][$style];
        }

        return $url;

    } else {
        return cmf_get_asset_url($file, false);
    }
}

/**
 * @TODO七牛
 * 获取文件下载链接
 * @param string $file
 * @param int $expires
 * @return string
 */
function cmf_get_file_download_url($file, $expires = 3600)
{
    return cmf_get_asset_url($file, false);
//    if (C('FILE_UPLOAD_TYPE') == 'Qiniu') {
//        $storage_setting = cmf_get_cmf_settings('storage');
//        $qiniu_setting   = $storage_setting['Qiniu']['setting'];
//        $filePath        = $qiniu_setting['protocol'] . '://' . $storage_setting['Qiniu']['domain'] . "/" . $file;
//        $url             = cmf_get_asset_url($file, false);
//
//        if ($qiniu_setting['enable_picture_protect']) {
//            $qiniuStorage = new \Think\Upload\Driver\Qiniu\QiniuStorage(C('UPLOAD_TYPE_CONFIG'));
//            $url          = $qiniuStorage->privateDownloadUrl($url, $expires);
//        }
//
//        return $url;
//
//    } else {
//        return cmf_get_asset_url($file, false);
//    }
}

/**
 * @deprecated
 * @param $string
 * @param string $operation
 * @param string $key
 * @param int $expiry
 * @return string
 */
function cmf_auth_code($string, $operation = 'DECODE', $key = '', $expiry = 0)
{
    $ckey_length = 4;

    $key  = md5($key ? $key : config("authcode"));
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey   = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);

    $string        = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);

    $result = '';
    $box    = range(0, 255);

    $rndkey = [];
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for ($j = $i = 0; $i < 256; $i++) {
        $j       = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp     = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a       = ($a + 1) % 256;
        $j       = ($j + $box[$a]) % 256;
        $tmp     = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result  .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace('=', '', base64_encode($result));
    }

}

/**
 * @deprecated
 * @param $string
 * @return string
 */
function cmf_auth_encode($string)
{
    return cmf_auth_code($string, "ENCODE");
}

/**
 * TODO
 * 获取文件相对路径
 * @param string $assetUrl 文件的URL
 * @return string
 */
function cmf_asset_relative_url($assetUrl)
{
    if (strpos($assetUrl, "http") === 0) {
        return $assetUrl;
    } else {
        return str_replace('/upload/', '', $assetUrl);
    }
}

/**
 * TODO
 * @param $content
 * @param string $pagetpl
 * @return mixed
 */
function cmf_content_page($content, $pagetpl = '{first}{prev}{liststart}{list}{listend}{next}{last}')
{
    $contents  = explode('_ueditor_page_break_tag_', $content);
    $totalsize = count($contents);
    import('Page');
    $pagesize  = 1;
    $PageParam = C("VAR_PAGE");
    $page      = new \Page($totalsize, $pagesize);
    $page->setLinkWraper("li");
    $page->SetPager('default', $pagetpl, ["listlong" => "9", "first" => "首页", "last" => "尾页", "prev" => "上一页", "next" => "下一页", "list" => "*", "disabledclass" => ""]);
    $content         = $contents[$page->firstRow];
    $data['content'] = $content;
    $data['page']    = $page->show('default');

    return $data;
}

/**
 * 检查用户对某个url,内容的可访问性，用于记录如是否赞过，是否访问过等等;开发者可以自由控制，对于没有必要做的检查可以不做，以减少服务器压力
 * @param string $object 访问对象的id,格式：不带前缀的表名+id;如posts1表示xx_posts表里id为1的记录;如果object为空，表示只检查对某个url访问的合法性
 * @param int $countLimit 访问次数限制,如1，表示只能访问一次
 * @param boolean $ipLimit ip限制,false为不限制，true为限制
 * @param int $expire 距离上次访问的最小时间单位s，0表示不限制，大于0表示最后访问$expire秒后才可以访问
 * @return true 可访问，false不可访问
 */
function cmf_check_user_action($object = "", $countLimit = 1, $ipLimit = false, $expire = 0)
{
    $request = request();
    $action  = $request->module() . "/" . $request->controller() . "/" . $request->action();
    $userId  = cmf_get_current_user_id();

    $ip = get_client_ip(0, true);//修复ip获取

    $where = ["user_id" => $userId, "action" => $action, "object" => $object];

    if ($ipLimit) {
        $where['ip'] = $ip;
    }

    $findLog = Db::name('user_action_log')->where($where)->find();

    $time = time();
    if ($findLog) {
        Db::name('user_action_log')->where($where)->update([
            "count"           => ["exp", "count+1"],
            "last_visit_time" => $time,
            "ip"              => $ip
        ]);

        if ($findLog['count'] >= $countLimit) {
            return false;
        }

        if ($expire > 0 && ($time - $findLog['last_visit_time']) < $expire) {
            return false;
        }
    } else {
        Db::name('user_action_log')->insert([
            "user_id"         => $userId,
            "action"          => $action,
            "object"          => $object,
            "count"           => ["exp", "count+1"],
            "last_visit_time" => $time, "ip" => $ip
        ]);
    }

    return true;
}

/**
 * @param $url
 * @return mixed|string
 */
function cmf_get_relative_url($url)
{
    if (strpos($url, "http") === 0) {
        $url = str_replace(["https://", "http://"], "", $url);

        $pos = strpos($url, "/");
        if ($pos === false) {
            return "";
        } else {
            $url  = substr($url, $pos + 1);
            $root = preg_replace("/^\//", "", cmf_get_root());
            $root = str_replace("/", "\/", $root);
            $url  = preg_replace("/^" . $root . "\//", "", $url);
            return $url;
        }
    }
    return $url;
}

/**
 * 判断是否为手机访问
 * @return  boolean
 */
function cmf_is_mobile()
{
    static $cmf_is_mobile;

    if (isset($cmf_is_mobile))
        return $cmf_is_mobile;

    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        $cmf_is_mobile = false;
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false // many mobile devices (all iPhone, iPad, etc.)
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mobi') !== false
    ) {
        $cmf_is_mobile = true;
    } else {
        $cmf_is_mobile = false;
    }

    return $cmf_is_mobile;
}

/**
 * 判断是否为微信访问
 * @return boolean
 */
function cmf_is_wechat()
{
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
        return true;
    }
    return false;
}

/**
 * 处理插件钩子
 * @param string $hook 钩子名称
 * @param mixed $params 传入参数
 * @return void
 */
function hook($hook, &$params = null, $extra = null)
{
    \think\Hook::listen($hook, $params);
}

/**
 * 处理插件钩子,只执行一个
 * @param string $hook 钩子名称
 * @param mixed $params 传入参数
 * @return void
 */
function hook_one($hook, &$params = null, $extra = null)
{
    return \think\Hook::listen($hook, $params, $extra, true);
}


/**
 * 获取插件类的类名
 * @param string $name 插件名
 * @return string
 */
function cmf_get_plugin_class($name)
{
    $pluginDir = cmf_parse_name($name);
    $class     = "plugins\\{$pluginDir}\\{$name}Plugin";
    return $class;
}

/**
 * 获取插件类的配置
 * @param string $name 插件名
 * @return array
 */
function cmf_get_plugin_config($name)
{
    $class = cmf_get_plugin_class($name);
    if (class_exists($class)) {
        $plugin = new $class();
        return $plugin->getConfig();
    } else {
        return [];
    }
}

/**
 * 替代scan_dir的方法
 * @param string $pattern 检索模式 搜索模式 *.txt,*.doc; (同glog方法)
 * @param int $flags
 * @param $pattern
 * @return array
 */
function cmf_scan_dir($pattern, $flags = null)
{
    $files = array_map('basename', glob($pattern, $flags));
    return $files;
}

function cmf_sub_dirs($dir)
{
    $dir     = ltrim($dir, "/");
    $dirs    = [];
    $subDirs = cmf_scan_dir("$dir/*", GLOB_ONLYDIR);
    if (!empty($subDirs)) {
        foreach ($subDirs as $subDir) {
            $subDir = "$dir/$subDir";
            array_push($dirs, $subDir);
            $subDirSubDirs = cmf_sub_dirs($subDir);
            if (!empty($subDirSubDirs)) {
                $dirs = array_merge($dirs, $subDirSubDirs);
            }
        }
    }

    return $dirs;
}

/**
 * 生成访问插件的url
 * @param string $url url格式：插件名://控制器名/方法
 * @param array $param 参数
 * @param bool $domain
 * @return string
 */
function cmf_plugin_url($url, $param = [], $domain = false)
{
    $url              = parse_url($url);
    $case_insensitive = true;
    $plugin           = $case_insensitive ? Loader::parseName($url['scheme']) : $url['scheme'];
    $controller       = $case_insensitive ? Loader::parseName($url['host']) : $url['host'];
    $action           = trim($case_insensitive ? strtolower($url['path']) : $url['path'], '/');

    /* 解析URL带的参数 */
    if (isset($url['query'])) {
        parse_str($url['query'], $query);
        $param = array_merge($query, $param);
    }

    /* 基础参数 */
    $params = [
        '_plugin'     => $plugin,
        '_controller' => $controller,
        '_action'     => $action,
    ];
    $params = array_merge($params, $param); //添加额外参数

    return url('\\cmf\\controller\\PluginController@index', $params, true, $domain);
}

/**
 * TODO
 * 检查权限
 * @param $userId  int           认证用户的id
 * @param $name string|array  需要验证的规则列表,支持逗号分隔的权限规则或索引数组
 * @param $relation string    如果为 'or' 表示满足任一条规则即通过验证;如果为 'and'则表示需满足所有规则才能通过验证
 * @return boolean           通过验证返回true;失败返回false
 */
function cmf_auth_check($userId, $name = null, $relation = 'or')
{
    if (empty($userId)) {
        return false;
    }

    if ($userId == 1) {
        return true;
    }

    $authObj = new \cmf\lib\Auth();
    if (empty($name)) {
        $module     = request()->module();
        $controller = request()->controller();
        $action     = request()->action();
        $name       = strtolower($module . "/" . $controller . "/" . $action);
    }
    return $authObj->check($userId, $name, $relation);
}

function cmf_alpha_id($in, $to_num = false, $pad_up = 4, $passKey = null)
{
    $index = "aBcDeFgHiJkLmNoPqRsTuVwXyZAbCdEfGhIjKlMnOpQrStUvWxYz0123456789";
    if ($passKey !== null) {
        // Although this function's purpose is to just make the
        // ID short - and not so much secure,
        // with this patch by Simon Franz (http://blog.snaky.org/)
        // you can optionally supply a password to make it harder
        // to calculate the corresponding numeric ID

        for ($n = 0; $n < strlen($index); $n++) $i[] = substr($index, $n, 1);

        $passhash = hash('sha256', $passKey);
        $passhash = (strlen($passhash) < strlen($index)) ? hash('sha512', $passKey) : $passhash;

        for ($n = 0; $n < strlen($index); $n++) $p[] = substr($passhash, $n, 1);

        array_multisort($p, SORT_DESC, $i);
        $index = implode($i);
    }

    $base = strlen($index);

    if ($to_num) {
        // Digital number  <<--  alphabet letter code
        $in  = strrev($in);
        $out = 0;
        $len = strlen($in) - 1;
        for ($t = 0; $t <= $len; $t++) {
            $bcpow = pow($base, $len - $t);
            $out   = $out + strpos($index, substr($in, $t, 1)) * $bcpow;
        }

        if (is_numeric($pad_up)) {
            $pad_up--;
            if ($pad_up > 0) $out -= pow($base, $pad_up);
        }
        $out = sprintf('%F', $out);
        $out = substr($out, 0, strpos($out, '.'));
    } else {
        // Digital number  -->>  alphabet letter code
        if (is_numeric($pad_up)) {
            $pad_up--;
            if ($pad_up > 0) $in += pow($base, $pad_up);
        }

        $out = "";
        for ($t = floor(log($in, $base)); $t >= 0; $t--) {
            $bcp = pow($base, $t);
            $a   = floor($in / $bcp) % $base;
            $out = $out . substr($index, $a, 1);
            $in  = $in - ($a * $bcp);
        }
        $out = strrev($out); // reverse
    }

    return $out;
}

/**
 * 验证码检查，验证完后销毁验证码
 * @param string $value
 * @param string $id
 * @return bool
 */
function cmf_captcha_check($value, $id = "")
{
    $captcha = new \think\captcha\Captcha();
    return $captcha->check($value, $id);
}

/**
 * TODO
 * 执行SQL文件  sae 环境下file_get_contents() 函数好像有间歇性bug。
 * @param string $sqlPath sql文件路径
 * @author 5iymt <1145769693@qq.com>
 */
function cmf_execute_sql_file($sqlPath)
{

    // 读取SQL文件
    $sql = file_get_contents($sqlPath);
    $sql = str_replace("\r", "\n", $sql);
    $sql = explode(";\n", $sql);

    // 替换表前缀
    $orginal = 'cmf_';
    $prefix  = C('DB_PREFIX');
    $sql     = str_replace("{$orginal}", "{$prefix}", $sql);

    // 开始安装
    foreach ($sql as $value) {
        $value = trim($value);
        if (empty ($value)) {
            continue;
        }
        $res = M()->execute($value);
    }
}

/**
 * TODO
 * 插件R方法扩展 建立多插件之间的互相调用。提供无限可能
 * 使用方式 get_plugns_return('Chat://Index/index',[])
 * @param string $url 调用地址
 * @param array $params 调用参数
 * @author 5iymt <1145769693@qq.com>
 */
function cmf_get_plugin_return($url, $params = [])
{
    $url        = parse_url($url);
    $case       = C('URL_CASE_INSENSITIVE');
    $plugin     = $case ? parse_name($url['scheme']) : $url['scheme'];
    $controller = $case ? parse_name($url['host']) : $url['host'];
    $action     = trim($case ? strtolower($url['path']) : $url['path'], '/');

    /* 解析URL带的参数 */
    if (isset($url['query'])) {
        parse_str($url['query'], $query);
        $params = array_merge($query, $params);
    }
    return R("plugins://{$plugin}/{$controller}/{$action}", $params);
}

/**
 * @deprecated
 * 判断当前的语言包，并返回语言包名
 */
function cmf_check_lang()
{
    $langSet = C('DEFAULT_LANG');
    if (C('LANG_SWITCH_ON', null, false)) {

        $varLang  = C('VAR_LANGUAGE', null, 'l');
        $langList = C('LANG_LIST', null, 'zh-cn');
        // 启用了语言包功能
        // 根据是否启用自动侦测设置获取语言选择
        if (C('LANG_AUTO_DETECT', null, true)) {
            if (isset($_GET[$varLang])) {
                $langSet = $_GET[$varLang];// url中设置了语言变量
                cookie('think_language', $langSet, 3600);
            } elseif (cookie('think_language')) {// 获取上次用户的选择
                $langSet = cookie('think_language');
            } elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {// 自动侦测浏览器语言
                preg_match('/^([a-z\d\-]+)/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches);
                $langSet = $matches[1];
                cookie('think_language', $langSet, 3600);
            }
            if (false === stripos($langList, $langSet)) { // 非法语言参数
                $langSet = C('DEFAULT_LANG');
            }
        }
    }

    return strtolower($langSet);

}

/**
 * 获取惟一订单号
 * @return string
 */
function cmf_get_order_sn()
{
    return date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
}

/**
 * 获取文件扩展名
 * @param string $filename
 * @return string
 */
function cmf_get_file_extension($filename)
{
    $pathinfo = pathinfo($filename);
    return strtolower($pathinfo['extension']);
}

/**
 * 检查手机或邮箱是否还可以发送验证码,并返回生成的验证码
 * @param string $account
 * @param integer $length 验证码位数,支持4,6,8
 * @return string 数字验证码
 */
function cmf_get_verification_code($account, $length = 6)
{
    if (empty($account)) return false;
    $verificationCodeQuery = Db::name('verification_code');
    $currentTime           = time();
    $maxCount              = 5;
    $findVerificationCode  = $verificationCodeQuery->where('account', $account)->find();
    $result                = false;
    if (empty($findVerificationCode)) {
        $result = true;
    } else {
        $sendTime       = $findVerificationCode['send_time'];
        $todayStartTime = strtotime(date('Y-m-d', $currentTime));
        if ($sendTime < $todayStartTime) {
            $result = true;
        } else if ($findVerificationCode['count'] < $maxCount) {
            $result = true;
        }
    }

    if ($result) {
        switch ($length) {
            case 4:
                $result = rand(1000, 9999);
                break;
            case 6:
                $result = rand(100000, 999999);
                break;
            case 8:
                $result = rand(10000000, 99999999);
                break;
            default:
                $result = rand(100000, 999999);

        }

    }

    return $result;
}


/**
 * 更新手机或邮箱验证码发送日志
 * @param string $account
 * @param string $code
 * @param int $expireTime
 * @return boolean
 */
function cmf_verification_code_log($account, $code, $expireTime = 0)
{
    $currentTime           = time();
    $expireTime            = $expireTime > $currentTime ? $expireTime : $currentTime + 30 * 60;
    $verificationCodeQuery = Db::name('verification_code');
    $findVerificationCode  = $verificationCodeQuery->where('account', $account)->find();
    if ($findVerificationCode) {
        $todayStartTime = strtotime(date("Y-m-d"));//当天0点
        if ($findVerificationCode['send_time'] <= $todayStartTime) {
            $count = 1;
        } else {
            $count = ['exp', 'count+1'];
        }
        $result = $verificationCodeQuery
            ->where('account', $account)
            ->update([
                'send_time'   => $currentTime,
                'expire_time' => $expireTime,
                'code'        => $code,
                'count'       => $count
            ]);
    } else {
        $result = $verificationCodeQuery
            ->insert([
                'account'     => $account,
                'send_time'   => $currentTime,
                'code'        => $code,
                'count'       => 1,
                'expire_time' => $expireTime
            ]);
    }

    return $result;
}

/**
 * 手机或邮箱验证码检查，验证完后销毁验证码增加安全性,返回true验证码正确，false验证码错误
 * @param string $account
 * @param string $code
 * @param boolean $clear 是否验证后销毁验证码
 * @return string  错误消息,空字符串代码验证码正确
 */
function cmf_check_verification_code($account, $code, $clear = false)
{
    $verificationCodeQuery = Db::name('verification_code');
    $findVerificationCode  = $verificationCodeQuery->where('account', $account)->find();

    if ($findVerificationCode) {
        if ($findVerificationCode['expire_time'] > time()) {

            if ($code == $findVerificationCode['code']) {
                if ($clear) {
                    $verificationCodeQuery->where('account', $account)->update(['code' => '']);
                }
            } else {
                return "验证码不正确!";
            }
        } else {
            return "验证码已经过期,请先获取验证码!";
        }

    } else {
        return "请先获取验证码!";
    }

    return "";
}

/**
 * 清空某个账号的数字验证码,一般在验证码验证正确完成后
 * @param string $account
 * @return boolean true：手机验证码正确，false：手机验证码错误
 */
function cmf_clear_verification_code($account)
{
    $verificationCodeQuery = Db::name('verification_code');
    $verificationCodeQuery->where('account', $account)->update(['code' => '']);
}

/**
 * 区分大小写的文件存在判断
 * @param string $filename 文件地址
 * @return boolean
 */
function file_exists_case($filename)
{
    if (is_file($filename)) {
        if (IS_WIN && APP_DEBUG) {
            if (basename(realpath($filename)) != basename($filename))
                return false;
        }
        return true;
    }
    return false;
}

/**
 * 生成用户 token
 * @param $userId
 * @param $deviceType
 * @return string 用户 token
 */
function cmf_generate_user_token($userId, $deviceType)
{
    $userTokenQuery = Db::name("user_token")
        ->where('user_id', $userId)
        ->where('device_type', $deviceType);
    $findUserToken  = $userTokenQuery->find();
    $currentTime    = time();
    $expireTime     = $currentTime + 24 * 3600 * 180;
    $token          = md5(uniqid()) . md5(uniqid());
    if (empty($findUserToken)) {
        Db::name("user_token")->insert([
            'token'       => $token,
            'user_id'     => $userId,
            'expire_time' => $expireTime,
            'create_time' => $currentTime,
            'device_type' => $deviceType
        ]);
    } else {
        Db::name("user_token")
            ->where('user_id', $userId)
            ->where('device_type', $deviceType)
            ->update([
                'token'       => $token,
                'expire_time' => $expireTime,
                'create_time' => $currentTime
            ]);
    }

    return $token;
}

/**
 * 字符串命名风格转换
 * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
 * @param string $name 字符串
 * @param integer $type 转换类型
 * @param bool $ucfirst 首字母是否大写（驼峰规则）
 * @return string
 */
function cmf_parse_name($name, $type = 0, $ucfirst = true)
{
    return Loader::parseName($name, $type, $ucfirst);
}

function cmf_curl_get($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    $SSL = substr($url, 0, 8) == "https://" ? true : false;
    if ($SSL) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 检查证书中是否设置域名
    }
    $content = curl_exec($ch);

    return $content;
}

/**
 * 判断字符串是否为已经序列化过
 * @param $str
 * @return bool
 */
function cmf_is_serialized($str)
{
    return ($str == serialize(false) || @unserialize($str) !== false);
}

/**
 * 判断是否SSL协议
 * @return boolean
 */
function cmf_is_ssl()
{
    if (isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))) {
        return true;
    } elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
        return true;
    }
    return false;
}


/**
 * 获取CMF系统的设置，此类设置用于全局
 * @param string $key 设置key，为空时返回所有配置信息
 * @return mixed
 */
function cmf_get_cmf_settings($key = "")
{
    $cmfSettings = cache("cmf_settings");
    if (empty($cmfSettings)) {
        $objOptions = new \app\admin\model\OptionModel();
        $objResult  = $objOptions->where("option_name", 'cmf_settings')->find();
        $arrOption  = $objResult ? $objResult->toArray() : [];
        if ($arrOption) {
            $cmfSettings = json_decode($arrOption['option_value'], true);
        } else {
            $cmfSettings = [];
        }
        cache("cmf_settings", $cmfSettings);
    }

    if (!empty($key)) {
        if (isset($cmfSettings[$key])) {
            return $cmfSettings[$key];
        } else {
            return false;
        }
    }
    return $cmfSettings;
}

/**
 * 判读是否sae环境
 * @return bool
 */
function cmf_is_sae()
{
    if (function_exists('saeAutoLoader')) {
        return true;
    } else {
        return false;
    }
}

/**
 * 文件写入
 * @todo sae环境还没有测试，你们如果有人有机会测试，测试完了帮忙删掉todo
 * @param $file
 * @param $content
 * @return bool|int
 */
function cmf_file_write($file, $content)
{

    if (cmf_is_sae()) {
        $s         = new SaeStorage();
        $arr       = explode('/', ltrim($file, './'));
        $domain    = array_shift($arr);
        $save_path = implode('/', $arr);
        return $s->write($domain, $save_path, $content);
    } else {
        return file_put_contents($file, $content);
    }
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
 * @return mixed
 */
function get_client_ip($type = 0, $adv = false)
{
    return request()->ip($type, $adv);
}

/**
 * 生成base64的url,用于数据库存放 url
 * @param $url
 * @param $params
 * @return string
 */
function cmf_url_encode($url, $params)
{
    // 解析参数
    if (is_string($params)) {
        // aaa=1&bbb=2 转换成数组
        parse_str($params, $params);
    }

    return base64_encode(json_encode(['action' => $url, 'param' => $params]));
}

/**
 * CMF Url生成
 * @param string $url 路由地址
 * @param string|array $vars 变量
 * @param bool|string $suffix 生成的URL后缀
 * @param bool|string $domain 域名
 * @return string
 */
function cmf_url($url = '', $vars = '', $suffix = true, $domain = false)
{
    static $routes;

    if (empty($routes)) {
        $routes = cache("routes");
    }

    if (false === strpos($url, '://') && 0 !== strpos($url, '/')) {
        $info = parse_url($url);
        $url  = !empty($info['path']) ? $info['path'] : '';
        if (isset($info['fragment'])) {
            // 解析锚点
            $anchor = $info['fragment'];
            if (false !== strpos($anchor, '?')) {
                // 解析参数
                list($anchor, $info['query']) = explode('?', $anchor, 2);
            }
            if (false !== strpos($anchor, '@')) {
                // 解析域名
                list($anchor, $domain) = explode('@', $anchor, 2);
            }
        } elseif (strpos($url, '@') && false === strpos($url, '\\')) {
            // 解析域名
            list($url, $domain) = explode('@', $url, 2);
        }
    }

    // 解析参数
    if (is_string($vars)) {
        // aaa=1&bbb=2 转换成数组
        parse_str($vars, $vars);
    }

    if (isset($info['query'])) {
        // 解析地址里面参数 合并到vars
        parse_str($info['query'], $params);
        $vars = array_merge($params, $vars);
    }

    if (!empty($vars) && !empty($routes[$url])) {

        foreach ($routes[$url] as $actionRoute) {
            $sameVars = array_intersect($vars, $actionRoute['vars']);

            if (count($sameVars) == count($actionRoute['vars'])) {
                ksort($sameVars);
                $url  = $url . '?' . http_build_query($sameVars);
                $vars = array_diff($vars, $sameVars);
                break;
            }
        }
    }

    if (!empty($anchor)) {
        $url = $url . '#' . $anchor;
    }

    if (!empty($domain)) {
        $url = $url . '@' . $domain;
    }

    return Url::build($url, $vars, $suffix, $domain);
}

/**
 * 判断 cmf 是否已经安装
 * @return bool
 */
function cmf_is_installed()
{
    static $cmfIsInstalled;
    if (empty($cmfIsInstalled)) {
        $cmfIsInstalled = file_exists(CMF_ROOT . 'data/install.lock');
    }

    return $cmfIsInstalled;
}