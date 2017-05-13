<?php

namespace cmf\lib\storage;

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

class Qiniu
{

    private $config;

    private $storageRoot;

    /**
     * Qiniu constructor.
     * @param $config
     */
    public function __construct($config)
    {
        $this->config      = $config;
        $this->storageRoot = $this->config['setting']['protocol'] . '://' . $this->config['domain'] . '/';
    }

    /**
     * @param $file
     * @param $filePath
     * @return array
     */
    public function upload($file, $filePath)
    {
        $accessKey = $this->config['accessKey'];
        $secretKey = $this->config['secretKey'];
        $upManager = new UploadManager();
        $auth      = new Auth($accessKey, $secretKey);
        $token     = $auth->uploadToken($this->config['bucket']);

        $result = $upManager->putFile($token, $file, $filePath);

        return [
            'preview_url' => $this->getPreviewUrl($file),
            'url'         => $this->getUrl($file),
        ];
    }

    /**
     * @param $file
     * @param string $style
     * @return string
     */
    public function getPreviewUrl($file, $style = '')
    {
        $style = empty($style) ? 'watermark' : $style;

        $url = $this->getUrl($file, $style);


        return $url;
    }

    /**
     * @param $file
     * @param string $style
     * @return string
     */
    public function getUrl($file, $style = '')
    {
        $style = empty($style) ? 'watermark' : $style;

        $config       = $this->config;
        $qiniuSetting = $config['setting'];
        $url          = $this->storageRoot . $file;

        if (!empty($style)) {
            $url = $url . $qiniuSetting['style_separator'] . $style;
        }

        return $url;
    }

    /**
     * @param $file
     * @param int $expires
     * @return mixed
     */
    public function getFileDownloadUrl($file, $expires = 3600)
    {
        $accessKey = $this->config['accessKey'];
        $secretKey = $this->config['secretKey'];
        $auth      = new Auth($accessKey, $secretKey);
        $url       = $this->getUrl($file);
        return $auth->privateDownloadUrl($url, $expires);
    }
}