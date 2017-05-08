<?php

namespace app\install\controller;

use think\Controller;
use think\Db;

class IndexController extends Controller
{

    public function _initialize()
    {
        if (file_exists(CMF_ROOT . "data/install.lock")) {
            $this->error('网站已经安装', cmf_get_root() . '/');
        }
    }

    // 安装首页
    public function index()
    {
        return $this->fetch(":index");
    }

    public function step2()
    {
//        if (file_exists_case('data/conf/config.php')) {
//            @unlink('data/conf/config.php');
//        }
        $data               = [];
        $data['phpversion'] = @phpversion();
        $data['os']         = PHP_OS;
        $tmp                = function_exists('gd_info') ? gd_info() : [];
        $server             = $_SERVER["SERVER_SOFTWARE"];
        $host               = (empty($_SERVER["SERVER_ADDR"]) ? $_SERVER["SERVER_HOST"] : $_SERVER["SERVER_ADDR"]);
        $name               = $_SERVER["SERVER_NAME"];
        $max_execution_time = ini_get('max_execution_time');
        $allow_reference    = (ini_get('allow_call_time_pass_reference') ? '<font color=green>[√]On</font>' : '<font color=red>[×]Off</font>');
        $allow_url_fopen    = (ini_get('allow_url_fopen') ? '<font color=green>[√]On</font>' : '<font color=red>[×]Off</font>');
        $safe_mode          = (ini_get('safe_mode') ? '<font color=red>[×]On</font>' : '<font color=green>[√]Off</font>');

        $err = 0;
        if (empty($tmp['GD Version'])) {
            $gd = '<font color=red>[×]Off</font>';
            $err++;
        } else {
            $gd = '<font color=green>[√]On</font> ' . $tmp['GD Version'];
        }

        if (class_exists('pdo')) {
            $data['pdo'] = '<i class="fa fa-check correct"></i> 已开启';
        } else {
            $data['pdo'] = '<i class="fa fa-remove error"></i> 未开启';
            $err++;
        }

        if (extension_loaded('pdo_mysql')) {
            $data['pdo_mysql'] = '<i class="fa fa-check correct"></i> 已开启';
        } else {
            $data['pdo_mysql'] = '<i class="fa fa-remove error"></i> 未开启';
            $err++;
        }

        if (extension_loaded('curl')) {
            $data['curl'] = '<i class="fa fa-check correct"></i> 已开启';
        } else {
            $data['curl'] = '<i class="fa fa-remove error"></i> 未开启';
            $err++;
        }

        if (extension_loaded('gd')) {
            $data['gd'] = '<i class="fa fa-check correct"></i> 已开启';
        } else {
            $data['gd'] = '<i class="fa fa-remove error"></i> 未开启';
            if (function_exists('imagettftext')) {
                $data['gd'] .= '<br><i class="fa fa-remove error"></i> FreeType Support未开启';
            }
            $err++;
        }

        if (extension_loaded('mbstring')) {
            $data['mbstring'] = '<i class="fa fa-check correct"></i> 已开启';
        } else {
            $data['mbstring'] = '<i class="fa fa-remove error"></i> 未开启';
            if (function_exists('imagettftext')) {
                $data['mbstring'] .= '<br><i class="fa fa-remove error"></i> FreeType Support未开启';
            }
            $err++;
        }

        if (ini_get('file_uploads')) {
            $data['upload_size'] = '<i class="fa fa-check correct"></i> ' . ini_get('upload_max_filesize');
        } else {
            $data['upload_size'] = '<i class="fa fa-remove error"></i> 禁止上传';
        }

        if (function_exists('session_start')) {
            $data['session'] = '<i class="fa fa-check correct"></i> 支持';
        } else {
            $data['session'] = '<i class="fa fa-remove error"></i> 不支持';
            $err++;
        }

        $folders    = [
            realpath(CMF_ROOT . 'data').'/',
            realpath(CMF_ROOT . 'data/conf').'/',
            realpath(CMF_ROOT . 'data/runtime').'/',
            realpath(CMF_ROOT . 'data/runtime/cache').'/',
            realpath(CMF_ROOT . 'data/runtime/log').'/',
            realpath(CMF_ROOT . 'data/runtime/temp').'/',
            realpath(CMF_ROOT . 'data/runtime/upload').'/',
            realpath( './upload').'/',
        ];
        $newFolders = [];
        foreach ($folders as $dir) {
            $testDir =  $dir;
            sp_dir_create($testDir);
            if (sp_testwrite($testDir)) {
                $newFolders[$dir]['w'] = true;
            } else {
                $newFolders[$dir]['w'] = false;
                $err++;
            }
            if (is_readable($testDir)) {
                $newFolders[$dir]['r'] = true;
            } else {
                $newFolders[$dir]['r'] = false;
                $err++;
            }
        }
        $data['folders'] = $newFolders;

        $this->assign($data);
        return $this->fetch(":step2");
    }

    public function step3()
    {
        return $this->fetch(":step3");
    }

    public function step4()
    {
        if ($this->request->isPost()) {
            //创建数据库
            $dbConfig             = [];
            $dbConfig['type']     = "mysql";
            $dbConfig['hostname'] = $this->request->param('dbhost');
            $dbConfig['username'] = $this->request->param('dbuser');
            $dbConfig['password'] = $this->request->param('dbpw');
            $dbConfig['hostport'] = $this->request->param('dbport');
            $dbConfig['charset']  = $this->request->param('dbcharset', 'utf8mb4');
            $db                   = Db::connect($dbConfig);
            $dbName               = $this->request->param('dbname');
            $sql                  = "CREATE DATABASE IF NOT EXISTS `{$dbName}` DEFAULT CHARACTER SET " . $dbConfig['charset'];
            $db->execute($sql) || $this->error($db->getError());

            $dbConfig['database'] = $dbName;

            $dbConfig['prefix'] = $this->request->param('dbprefix', '', 'trim');

            session('install_db_config', $dbConfig);

            $sql = sp_split_sql('thinkcmf.sql', $dbConfig['prefix'], $dbConfig['charset']);
            session('install_sql', $sql);

            $this->assign('sql_count', count($sql));

            session('install_error', 0);

            $siteName    = $this->request->param('sitename');
            $seoKeywords = $this->request->param('sitekeywords');
            $siteInfo    = $this->request->param('siteinfo');

            session('install_site_info', [
                'site_name'            => $siteName,
                'site_seo_title'       => $siteName,
                'site_seo_keywords'    => $seoKeywords,
                'site_seo_description' => $siteInfo
            ]);

            $userLogin = $this->request->param('manager');
            $userPass  = $this->request->param('manager_pwd');
            $userEmail = $this->request->param('manager_email');

            session('admin_info', [
                'user_login' => $userLogin,
                'user_pass'  => $userPass,
                'user_email' => $userEmail
            ]);

            return $this->fetch(":step4");

        } else {
            exit;
        }
    }

    public function step5()
    {
        if (session("_install_step") == 4) {
            @touch(CMF_ROOT . 'data/install.lock');
            return $this->fetch(":step5");
        } else {
            $this->error("非法安装！");
        }
    }

    public function install()
    {
        $dbConfig = session('install_db_config');

        if (empty($dbConfig)) {
            $this->error("非法安装!");
        }

        $sqlIndex = $this->request->param('sql_index', 0, 'intval');

        $db = Db::connect($dbConfig);

        $sql = session('install_sql');

        if (empty($sql)) {
            $sql = sp_split_sql('thinkcmf.sql', $dbConfig['prefix']);
            session('install_sql', $sql);
        }

        if ($sqlIndex >= count($sql)) {
            $installError = session('install_error');
            $this->success("安装完成!", '', ['done' => 1, 'error' => $installError]);
        }

        $sqlToExec = $sql[$sqlIndex] . ';';

        $result = sp_execute_sql($db, $sqlToExec);

        if (!empty($result['error'])) {
            $installError = session('install_error');
            $installError = empty($installError) ? 0 : $installError;

            session('install_error', $installError + 1);
            $this->error($result['message'], '', [
                'sql'       => $sqlToExec,
                'exception' => $result['exception']
            ]);
        } else {
            $this->success($result['message'], '', [
                'sql' => $sqlToExec
            ]);
        }

    }

    public function setSite()
    {
        $dbConfig = session('install_db_config');

        if (empty($dbConfig)) {
            $this->error("非法安装!");
        }

        $siteInfo               = session('install_site_info');
        $admin                  = session('admin_info');
        $admin['id']            = 1;
        $admin['user_pass']     = cmf_password($admin['user_pass']);
        $admin['user_type']     = 1;
        $admin['create_time']   = time();
        $admin['user_status']   = 1;
        $admin['user_nickname'] = $admin['user_login'];

        try {
            cmf_set_option('site_info', $siteInfo);
            Db::name('user')->insert($admin);
            session("_install_step", 4);
            $this->success("网站创建完成!");
        } catch (\Exception $e) {
            $this->success("网站创建失败!");
        }

    }

    public function setDbConfig()
    {
        $dbConfig = session('install_db_config');

        $dbConfig['authcode'] = cmf_random_string(18);

        $result = sp_create_db_config($dbConfig);

        if ($result) {
            $this->success("数据配置文件写入成功!");
        } else {
            $this->error("数据配置文件写入失败!");
        }
    }

    public function testDbPwd()
    {
        if ($this->request->isPost()) {
            $dbConfig         = $this->request->param();
            $dbConfig['type'] = "mysql";

            try {
                Db::connect($dbConfig)->query("SELECT VERSION();");
            } catch (\Exception $e) {
                die("");
            }
            exit("1");
        } else {
            exit("need post!");
        }

    }

}

