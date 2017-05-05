<?php
function sp_testwrite($d)
{
    $tfile = "_test.txt";
    $fp    = @fopen($d . "/" . $tfile, "w");
    if (!$fp) {
        return false;
    }
    fclose($fp);
    $rs = @unlink($d . "/" . $tfile);
    if ($rs) {
        return true;
    }
    return false;
}

function sp_dir_create($path, $mode = 0777)
{
    if (is_dir($path))
        return true;
    $ftp_enable = 0;
    $path       = sp_dir_path($path);
    $temp       = explode('/', $path);
    $cur_dir    = '';
    $max        = count($temp) - 1;
    for ($i = 0; $i < $max; $i++) {
        $cur_dir .= $temp[$i] . '/';
        if (@is_dir($cur_dir))
            continue;
        @mkdir($cur_dir, 0777, true);
        @chmod($cur_dir, 0777);
    }
    return is_dir($path);
}

function sp_dir_path($path)
{
    $path = str_replace('\\', '/', $path);
    if (substr($path, -1) != '/')
        $path = $path . '/';
    return $path;
}

function sp_execute_sql($db, $file, $tablePre)
{
    //读取SQL文件
    $sql = file_get_contents(APP_PATH . 'install/data/' . $file);
    $sql = str_replace("\r", "\n", $sql);
    $sql = explode(";\n", $sql);

    //替换表前缀
    $defaultTablePre = "cmf_";
    $sql              = str_replace(" `{$defaultTablePre}", " `{$tablePre}", $sql);

    //开始安装
    sp_show_msg('开始安装数据库...');
    foreach ($sql as $item) {
        $item = trim($item);
        if (empty($item)) continue;
        preg_match('/CREATE TABLE `([^ ]*)`/', $item, $matches);
        if ($matches) {
            $table_name = $matches[1];
            $msg        = "创建数据表{$table_name}";
            if (false !== $db->execute($item)) {
                sp_show_msg($msg . ' 完成');
            } else {
                sp_show_msg($msg . ' 失败！', 'error');
            }
        } else {
            $db->execute($item);
        }

    }
}

/**
 * 显示提示信息
 * @param  string $msg 提示信息
 */
function sp_show_msg($msg, $class = '')
{
    echo "<script type=\"text/javascript\">showmsg(\"{$msg}\", \"{$class}\")</script>";
    flush();
    ob_flush();
}

function sp_update_site_configs($db, $table_prefix)
{
    $sitename        = I("post.sitename");
    $email           = I("post.manager_email");
    $siteurl         = I("post.siteurl");
    $seo_keywords    = I("post.sitekeywords");
    $seo_description = I("post.siteinfo");
    $site_options    = <<<helllo
            {
            		"site_name":"$sitename",
            		"site_host":"$siteurl",
            		"site_root":"",
            		"site_icp":"",
            		"site_admin_email":"$email",
            		"site_tongji":"",
            		"site_copyright":"",
            		"site_seo_title":"$sitename",
            		"site_seo_keywords":"$seo_keywords",
            		"site_seo_description":"$seo_description"
        }
helllo;
    $sql             = "INSERT INTO `{$table_prefix}options` (option_value,option_name) VALUES ('$site_options','site_options')";
    $db->execute($sql);
    sp_show_msg("网站信息配置成功!");
}

function sp_create_admin_account($db, $table_prefix, $authcode)
{
    $username    = I("post.manager");
    $password    = sp_password(I("post.manager_pwd"), $authcode);
    $email       = I("post.manager_email");
    $create_date = date("Y-m-d h:i:s");
    $ip          = get_client_ip(0, true);
    $sql         = <<<hello
    INSERT INTO `{$table_prefix}users` 
    (id,user_login,user_pass,user_nicename,user_email,user_url,create_time,user_activation_key,user_status,last_login_ip,last_login_time) VALUES 
    ('1', '{$username}', '{$password}', 'admin', '{$email}', '', '{$create_date}', '', '1', '{$ip}','{$create_date}');;
hello;
    $db->execute($sql);
    sp_show_msg("管理员账号创建成功!");
}


/**
 * 写入配置文件
 * @param  array $config 配置信息
 */
function sp_create_config($config, $authcode)
{
    if (is_array($config)) {
        //读取配置内容
        $conf = file_get_contents(MODULE_PATH . 'Data/config.php');

        //替换配置项
        foreach ($config as $key => $value) {
            $conf = str_replace("#{$key}#", $value, $conf);
        }

        $conf = str_replace('#AUTHCODE#', $authcode, $conf);
        $conf = str_replace('#COOKIE_PREFIX#', sp_random_string(6) . "_", $conf);

        //写入应用配置文件
        if (file_put_contents('data/conf/db.php', $conf)) {
            sp_show_msg('配置文件写入成功');
        } else {
            sp_show_msg('配置文件写入失败！', 'error');
        }
        return '';

    }
}
