<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace plugins\qiniu\controller; //Demo插件英文名，改成你的插件英文就行了
use cmf\controller\PluginBaseController;
use plugins\qiniu\lib\Qiniu;
use think\Validate;
use think\Db;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Qiniu\Http\Client;

class AssetController extends PluginBaseController
{

    function getUrl()
    {

        $qiniu = new Qiniu([]);

        $fileHash = $this->request->param('file_hash');
        $filname  = $this->request->param('filename');
        $fileType = $this->request->param('filetype');

        $suffix = cmf_get_file_extension($filname);

        $file = $fileHash . ".{$suffix}";

        $previewUrl = $fileType == 'image' ? $qiniu->getPreviewUrl($file) : $qiniu->getFileDownloadUrl($file);
        $url        = $fileType == 'image' ? $qiniu->getImageUrl($file, 'watermark') : $qiniu->getFileDownloadUrl($file);

        return $this->success('success', null, [
            'url'         => $url,
            'preview_url' => $previewUrl,
            'filepath'    => $file
        ]);
    }

    public function saveFile()
    {
        $userId = cmf_get_current_admin_id();
        $userId = $userId ? $userId : cmf_get_current_user_id();

        if (empty($userId)) {
            $this->error('error');
        }
        $validate = new Validate([
            'filename' => 'require',
            'file_key' => 'require',
        ]);

        $data = $this->request->param();

        $result = $validate->check($data);

        if ($result !== true) {
            $this->error($validate);
        }

        $fileKey = $data['file_key'];

        $suffix = cmf_get_file_extension($data['filename']);

        $config = $this->getPlugin()->getConfig();

        $accessKey = $config['accessKey'];
        $secretKey = $config['secretKey'];

        $auth = new Auth($accessKey, $secretKey);


        $client = new Client();

        $encodedEntryURISrc  = \Qiniu\base64_urlSafeEncode($config['bucket'] . ':' . $fileKey);
        $encodedEntryURIDest = \Qiniu\base64_urlSafeEncode($config['bucket'] . ':' . $fileKey . ".{$suffix}");

        $signingStr    = "/move/{$encodedEntryURISrc}/{$encodedEntryURIDest}";
        $authorization = $auth->signRequest($signingStr, '');

        $url = 'http://rs.qiniu.com/' . $signingStr;

        $response = $client->post($url, null, ['Authorization' => 'QBox ' . $authorization]);

        if ($response->statusCode == 612) {
            $this->error('文件不存在！');
        }

        if ($response->statusCode == 599) {
            $this->error('文件保存失败！');
        }

        $signingStr    = "/stat/{$encodedEntryURIDest}";
        $authorization = $auth->signRequest($signingStr, '');

        $url = 'http://rs.qiniu.com/' . $signingStr;

        $response = $client->get($url, ['Authorization' => 'QBox ' . $authorization]);

        if ($response->statusCode != 200) {
            $this->error('操作失败！');
        }

        $fileInfo = $response->json();

        $findAsset = Db::name('asset')->where('file_key', $fileKey)->find();


        if (empty($findAsset)) {

            Db::name('asset')->insert([
                'user_id'     => $userId,
                'file_size'   => $fileInfo['fsize'],
                'filename'    => $data['filename'],
                'create_time' => time(),
                'file_key'    => $fileKey,
                'file_path'   => $fileKey . ".{$suffix}",
                'suffix'      => $suffix
            ]);
        }

        $this->success('success');

    }

}
