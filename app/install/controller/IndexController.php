<?php
namespace app\install\controller;

use think\Controller;
use think\Db;

class IndexController extends Controller
{

    public function _initialize()
    {
//        if (file_exists_case("./data/install.lock")) {
//            redirect(__ROOT__ . "/");
//        }
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
        $data['phpversion'] = @ phpversion();
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
            'data',
            'data/conf',
            'data/runtime',
            'data/runtime/Cache',
            'data/runtime/Data',
            'data/runtime/Logs',
            'data/runtime/Temp',
            'data/upload',
        ];
        $newFolders = [];
        foreach ($folders as $dir) {
            $testDir = "./" . $dir;
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
            $db                   = Db::connect($dbConfig);
            $dbName               = $this->request->param('dbname');
            $sql                  = "CREATE DATABASE IF NOT EXISTS `{$dbName}` DEFAULT CHARACTER SET utf8mb4";
            $db->execute($sql) || $this->error($db->getError());

            echo $this->fetch(":step4");

            //创建数据表
            $dbConfig['database'] = $dbName;
            $dbConfig['prefix']   = $this->request->param('post.dbprefix', '', 'trim');
            $db                   = Db::connect($dbConfig);

            $tablePrefix = $this->request->param('dbprefix');
            sp_execute_sql($db, "thinkcmf.sql", $tablePrefix);


//            //更新配置信息
//            sp_update_site_configs($db, $tablePrefix);
//
//            $authCode = sp_random_string(18);
//            //创建管理员
//            sp_create_admin_account($db, $tablePrefix, $authCode);
//
//            //生成网站配置文件
//            sp_create_config($dbConfig, $authCode);
            session("_install_step", 4);
            sleep(1);
            $this->redirect("step5");
        } else {
            exit;
        }
    }

    public function step5()
    {
        if (session("_install_step") == 4) {
            @touch('./data/install.lock');
            return $this->fetch(":step5");
        } else {
            $this->error("非法安装！");
        }
    }

    public function testDbPwd()
    {
        if ($this->request->isPost()) {
            $dbConfig         = $this->request->param();
            $dbConfig['type'] = "mysql";

            try {
                Db::connect($dbConfig);
            } catch (\Exception $e) {
                die("");
            }
            exit("1");
        } else {
            exit("need post!");
        }

    }

}

