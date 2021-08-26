<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\install\controller;

use app\admin\logic\HookLogic;
use app\admin\logic\MenuLogic;
use app\admin\model\ThemeModel;
use app\user\logic\UserActionLogic;
use cmf\controller\BaseController;
use think\facade\Db;
use think\facade\Lang;

require_once __DIR__ . '/../common.php';

class IndexController extends BaseController
{
    protected function initialize()
    {
        if (cmf_is_installed()) {
            $this->error('网站已经安装', cmf_get_root() . '/');
        }

        if (!is_writable(CMF_DATA)) {
            echo '目录' . realpath(CMF_ROOT . 'data') . '无法写入！';
            abort(500, '目录' . realpath(CMF_ROOT . 'data') . '无法写入！');
        }

        $langSet = $this->app->lang->getLangSet();
        $this->app->lang->load([
            dirname(__DIR__) . '/lang/' . $langSet . ".php"
        ]);


    }

    protected function _initializeView()
    {
        $root           = cmf_get_root();
        $viewReplaceStr = [
            '__ROOT__'     => $root,
//            '__TMPL__'     => "{$root}/{$themePath}",
            '__STATIC__'   => "{$root}/static",
            '__WEB_ROOT__' => $root
        ];
        $this->view->engine()->config([
            'view_path'          => dirname(__DIR__) . '/view/',
            'tpl_replace_string' => $viewReplaceStr
        ]);
//        config('template.view_path', dirname(__DIR__) . '/view/');
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
//        $server             = $_SERVER["SERVER_SOFTWARE"];
//        $host               = $this->request->host();
//        $name               = $_SERVER["SERVER_NAME"];
//        $max_execution_time = ini_get('max_execution_time');
//        $allow_reference    = (ini_get('allow_call_time_pass_reference') ? '<font color=green>[√]On</font>' : '<font color=red>[×]Off</font>');
//        $allow_url_fopen    = (ini_get('allow_url_fopen') ? '<font color=green>[√]On</font>' : '<font color=red>[×]Off</font>');
//        $safe_mode          = (ini_get('safe_mode') ? '<font color=red>[×]On</font>' : '<font color=green>[√]Off</font>');

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

        if (extension_loaded('fileinfo')) {
            $data['fileinfo'] = '<i class="fa fa-check correct"></i> 已开启';
        } else {
            $data['fileinfo'] = '<i class="fa fa-remove error"></i> 未开启';
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

        if (version_compare(phpversion(), '5.6.0', '>=') && version_compare(phpversion(), '7.0.0', '<') && ini_get('always_populate_raw_post_data') != -1) {
            $data['always_populate_raw_post_data']          = '<i class="fa fa-remove error"></i> 未关闭';
            $data['show_always_populate_raw_post_data_tip'] = true;
            $err++;
        } else {

            $data['always_populate_raw_post_data'] = '<i class="fa fa-check correct"></i> 已关闭';
        }

        $folders    = [
            realpath(CMF_ROOT . 'data') . DIRECTORY_SEPARATOR,
            realpath('./plugins') . DIRECTORY_SEPARATOR,
            realpath('./themes') . DIRECTORY_SEPARATOR,
            realpath('./upload') . DIRECTORY_SEPARATOR,

        ];
        $newFolders = [];
        foreach ($folders as $dir) {
            $testDir = $dir;
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
        session(null);
        if ($this->request->isPost()) {
            //创建数据库
            $dbConfig             = [];
            $dbConfig['type']     = "mysql";
            $dbConfig['hostname'] = $this->request->param('dbhost');
            $dbConfig['username'] = $this->request->param('dbuser');
            $dbConfig['password'] = $this->request->param('dbpw');
            $dbConfig['hostport'] = $this->request->param('dbport');
            $dbConfig['charset']  = $this->request->param('dbcharset', 'utf8mb4');

            $userLogin = $this->request->param('manager');
            $userPass  = $this->request->param('manager_pwd');
            $userEmail = $this->request->param('manager_email');
            //检查密码。空 6-32字符。
            empty($userPass) && $this->error("密码不可以为空");
            strlen($userPass) < 6 && $this->error("密码长度最少6位");
            strlen($userPass) > 32 && $this->error("密码长度最多32位");

            $this->updateDbConfig($dbConfig);
            $db     = Db::connect('install_db');
            $dbName = $this->request->param('dbname');
            $sql    = "CREATE DATABASE IF NOT EXISTS `{$dbName}` DEFAULT CHARACTER SET " . $dbConfig['charset'];
            $db->execute($sql) || $this->error($db->getError());

            $dbConfig['database'] = $dbName;

            $dbConfig['prefix'] = $this->request->param('dbprefix', '', 'trim');

            session('install.db_config', $dbConfig);

            $sql  = cmf_split_sql(dirname(__DIR__) . '/data/thinkcmf.sql', $dbConfig['prefix'], $dbConfig['charset']);
            $apps = cmf_scan_dir(CMF_ROOT . 'app/*', GLOB_ONLYDIR);
            foreach ($apps as $app) {
                $appDbSqlFile = CMF_ROOT . "app/{$app}/data/{$app}.sql";
                if (file_exists($appDbSqlFile)) {
                    $sqlList = cmf_split_sql($appDbSqlFile, $dbConfig['prefix'], $dbConfig['charset']);
                    $sql     = array_merge($sql, $sqlList);
                }
            }

            session('install.sql', $sql);

            $this->assign('sql_count', count($sql));

            session('install.error', 0);

            $siteName    = $this->request->param('sitename');
            $seoKeywords = $this->request->param('sitekeywords');
            $siteInfo    = $this->request->param('siteinfo');

            session('install.site_info', [
                'site_name'            => $siteName,
                'site_seo_title'       => $siteName,
                'site_seo_keywords'    => $seoKeywords,
                'site_seo_description' => $siteInfo
            ]);

            session('install.admin_info', [
                'user_login' => $userLogin,
                'user_pass'  => $userPass,
                'user_email' => $userEmail
            ]);

            return $this->fetch(":step4");

        } else {
        }
    }

    public function install()
    {
        $dbConfig = session('install.db_config');
        $sql      = session('install.sql');

        if (empty($dbConfig) || empty($sql)) {
            $this->error("非法安装!");
        }

        $sqlIndex = $this->request->param('sql_index', 0, 'intval');

        $this->updateDbConfig($dbConfig);
        $db = Db::connect('install_db');

        if ($sqlIndex >= count($sql)) {
            $installError = session('install.error');
            $this->success("安装完成!", '', ['done' => 1, 'error' => $installError]);
        }

        $sqlToExec = $sql[$sqlIndex] . ';';

        $result = sp_execute_sql($db, $sqlToExec);

        if (!empty($result['error'])) {
            $installError = session('install.error');
            $installError = empty($installError) ? 0 : $installError;

            session('install.error', $installError + 1);
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

    public function setDbConfig()
    {
        $dbConfig = session('install.db_config');

        $dbConfig['authcode'] = cmf_random_string(18);

        $result = sp_create_db_config($dbConfig);

        if ($result) {
            $this->success("数据配置文件写入成功!");
        } else {
            $this->error("数据配置文件写入失败!");
        }
    }

    public function setSite()
    {
        $dbConfig = session('install.db_config');

        if (empty($dbConfig)) {
            $this->error("非法安装!");
        }

        $siteInfo               = session('install.site_info');
        $admin                  = session('install.admin_info');
        $admin['id']            = 1;
        $admin['user_pass']     = cmf_password($admin['user_pass']);
        $admin['user_type']     = 1;
        $admin['create_time']   = time();
        $admin['user_status']   = 1;
        $admin['user_nickname'] = $admin['user_login'];

        try {
            cmf_set_option('site_info', $siteInfo);
            Db::name('user')->insert($admin);
        } catch (\Exception $e) {
            $this->error("网站创建失败!" . $e->getMessage());
        }

        $this->success("网站创建完成!");

    }

    public function installTheme()
    {
        $themeModel = new ThemeModel();
        $result     = $themeModel->installTheme(config('template.cmf_default_theme'));
        if ($result === false) {
            $this->error('模板不存在!');
        }

//        session("install.step", 4);
        $this->success("模板安装成功");
    }

    public function installAppMenus()
    {
        $apps = cmf_scan_dir(CMF_ROOT . 'app/*', GLOB_ONLYDIR);
        foreach ($apps as $app) {
            // 导入后台菜单
            MenuLogic::importMenus($app);
        }

        $this->success("应用后台菜单导入成功");
    }

    public function installAppHooks()
    {
        $apps = cmf_scan_dir(CMF_ROOT . 'app/*', GLOB_ONLYDIR);
        foreach ($apps as $app) {

            // 导入应用钩子
            HookLogic::importHooks($app);
        }

        $this->success("应用钩子导入成功");
    }


    public function installAppUserActions()
    {
        $apps = cmf_scan_dir(CMF_ROOT . 'app/*', GLOB_ONLYDIR);
        foreach ($apps as $app) {
            // 导入应用用户行为
            UserActionLogic::importUserActions($app);
        }

        session("install.step", 4);
        $this->success("应用用户行为成功");
    }

    public function step5()
    {
        if (session("install.step") == 4) {
            @touch(CMF_DATA . 'install.lock');
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

            $this->updateDbConfig($dbConfig);

            $supportInnoDb = false;

            try {
                $engines = Db::connect('install_db')->query("SHOW ENGINES;");

                foreach ($engines as $engine) {
                    if ($engine['Engine'] == 'InnoDB' && $engine['Support'] != 'NO') {
                        $supportInnoDb = true;
                        break;
                    }
                }
            } catch (\Exception $e) {
                $this->error('数据库账号或密码不正确！' . $e->getMessage());
            }
            if ($supportInnoDb) {
                $this->success('验证成功！');
            } else {
                $this->error('数据库账号密码验证通过，但不支持InnoDb!');
            }
        } else {
            $this->error('非法请求方式！');
        }

    }

    public function testRewrite()
    {
        $this->success('success');
    }

    /**
     * 加载模板输出
     * @access protected
     * @param string $template 模板文件名
     * @param array  $vars     模板输出变量
     * @param array  $config   模板参数
     * @return mixed
     */
    protected function fetch($template = '', $vars = [], $config = [])
    {
        return $this->view->fetch($template, $vars, $config);;
    }

    private function updateDbConfig($dbConfig)
    {
        $oldDbConfig                              = config('database');
        $oldDbConfig['connections']['install_db'] = $dbConfig;
        config($oldDbConfig, 'database');
    }

}

