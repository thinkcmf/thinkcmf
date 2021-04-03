<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\logic\PluginLogic;
use app\admin\model\HookModel;
use app\admin\model\PluginModel;
use app\admin\model\HookPluginModel;
use cmf\paginator\Bootstrap;
use Composer\Semver\VersionParser;
use think\facade\Db;

/**
 * 应用市场
 * @adminMenuRoot(
 *     'name'   =>'应用中心',
 *     'action' =>'default',
 *     'parent' =>'',
 *     'display'=> true,
 *     'order'  => 20,
 *     'icon'   =>'cloud-download',
 *     'remark' =>'应用市场'
 * )
 */
class AppStoreController extends AppStoreAdminBaseController
{

    /**
     * 插件市场
     * @adminMenu(
     *     'name'   => '插件市场',
     *     'parent' => 'default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '插件市场',
     *     'param'  => ''
     * )
     */
    public function plugins()
    {
        $appStoreSettings = cmf_get_option('appstore_settings');
        $accessToken      = '';
        if (!empty($appStoreSettings['access_token'])) {
            $accessToken = $appStoreSettings['access_token'];
        }

        $currentPage = $this->request->param('page', 1, 'intval');
        $url         = "https://www.thinkcmf.com/api/appstore/plugins?token={$accessToken}&page=" . $currentPage;
        $data        = cmf_curl_get($url);

        $data = json_decode($data, true);
        $page = '';

        if (empty($data['code'])) {
            $plugins = [];
        } else {
            $plugins   = $data['data']['plugins'];
            $paginator = new Bootstrap([], 10, $currentPage, $data['data']['total'], false, ['path' => $this->request->baseUrl()]);
            $page      = $paginator->render();
        }

        $appstoreSettings = cmf_get_option('appstore_settings');

        $installedPlugins = Db::name('plugin')->column('*', 'name');

        $newPlugins = [];
        foreach ($plugins as $plugin) {
            $plugin['installed']   = 0;
            $plugin['need_update'] = 0;
            $pluginName            = cmf_parse_name($plugin['name'], 1);

            if (!empty($installedPlugins[$pluginName]) && is_dir(WEB_ROOT . 'plugins' . DIRECTORY_SEPARATOR . cmf_parse_name($plugin['name'], 0))) {
                $installedPlugin            = $installedPlugins[$pluginName];
                $plugin['installed']        = 1;
                $plugin['need_update']      = $installedPlugin['version'] == $plugin['version'] ? 0 : 1;
                $plugin['installed_plugin'] = $installedPlugin;
            }

            $newPlugins[] = $plugin;
        }

        $this->assign('plugins', $newPlugins);
        $this->assign('appstore_settings', $appstoreSettings);


        $this->assign('page', $page);
        return $this->fetch();
    }

    public function login()
    {
        return $this->fetch();
    }

    public function doLogin()
    {
        $accessToken = $this->request->param('access_token');
        if (empty($accessToken)) {
            $this->error('非法请求！');
        }

        $httpReferer = $this->request->server('HTTP_REFERER');
        if (empty($httpReferer)) {
            $this->error('非法请求！');
        }
        $httpReferer = parse_url($httpReferer);
        $domain      = empty($httpReferer['host']) ? '' : $httpReferer['host'];

        $data = cmf_curl_get("https://www.thinkcmf.com/api/appstore/login/refreshAccessToken?access_token={$accessToken}&domain=$domain");

        if (empty($data)) {
            $this->error('请求失败，请重新登录！');
        }

        $data = json_decode($data, true);

        if (!empty($data['data']['access_token'])) {
            cmf_set_option('appstore_settings', $data['data']);
        }

        $this->success('登录成功！');

    }

    public function installPlugin()
    {
        $appStoreSettings = cmf_get_option('appstore_settings');
        $accessToken      = '';
        if (!empty($appStoreSettings['access_token'])) {
            $accessToken = $appStoreSettings['access_token'];
        }
        $id      = $this->request->param('id', 0, 'intval');
        $version = $this->request->param('version', '', 'urlencode');
        $data    = cmf_curl_get("https://www.thinkcmf.com/api/appstore/plugins/{$id}?token=$accessToken&version=$version");
        $data    = json_decode($data, true);

        if (empty($data['code'])) {
            if (!empty($data['data']['code']) && $data['data']['code'] == 10001) {
                cmf_set_option('appstore_settings', ['access_token' => '']);
                $this->error($data['msg'], null, ['code' => 10001]);
            }

            if (!empty($data['data']['code']) && $data['data']['code'] == 10002) {
                $this->error($data['msg'], null, ['code' => 10002]);
            }

        } else {
            $tmpFileName = "plugin{$id}_" . time() . microtime() . '.zip';

            $tmpFileDir = CMF_DATA . 'download/';

            if (!is_dir($tmpFileDir)) {
                mkdir($tmpFileDir, 0777, true);
            }

            $tmpFile = $tmpFileDir . $tmpFileName;
            $fp = fopen($tmpFile, 'wb') or $this->error('操作失败！'); //新建或打开文件,将curl下载的文件写入文件

            $ch = curl_init($data['data']['plugin']['download_url']);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            $res = curl_exec($ch);
            curl_close($ch);
            fclose($fp);

            $pluginName = cmf_parse_name($data['data']['plugin']['name'], 0);

            $archive = new \PclZip($tmpFile);

            $files = $archive->listContent();


            foreach ($files as $mFile) {
                if (strpos($mFile['filename'], $pluginName) === 0) {
                    $result = $archive->extractByIndex($mFile['index'], PCLZIP_OPT_PATH, WEB_ROOT . 'plugins/');
                }
            }

            unlink($tmpFile);

            $pluginName = cmf_parse_name($pluginName, 1);

            if (empty($version)) {
                $result = PluginLogic::install($pluginName);
            } else {
                $result = PluginLogic::update($pluginName);
            }

            if ($result !== true) {
                $this->error($result);
            }

        }
        if (empty($version)) {
            $this->success('安装成功！');
        } else {
            $this->success('升级成功！');
        }
    }

//    public function test()
//    {
//        $versionParser = new VersionParser();
//        $result        = $versionParser->parseConstraints(">=1.0.0 || ~2.0");
//        print_r($result);
//    }


}
