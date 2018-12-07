<?php

namespace plugins\qiniu\lib;

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

class Qiniu
{

    private $config;

    private $storageRoot;

    /**
     * @var \plugins\qiniu\QiniuPlugin
     */
    private $plugin;

    /**
     * Qiniu constructor.
     * @param $config
     */
    public function __construct($config)
    {
        $pluginClass = cmf_get_plugin_class('Qiniu');

        $this->plugin = new $pluginClass();
        $this->config = $this->plugin->getConfig();

        $this->storageRoot = $this->config['protocol'] . '://' . $this->config['domain'] . '/';
    }

    /**
     * 文件上传
     * @param string $file 上传文件路径
     * @param string $filePath 文件路径相对于upload目录
     * @param string $fileType 文件类型,image,video,audio,file
     * @param array $param 额外参数
     * @return mixed
     */
    public function upload($file, $filePath, $fileType = 'image', $param = null)
    {
        $accessKey = $this->config['accessKey'];
        $secretKey = $this->config['secretKey'];
        $watermark = $this->config['styles_watermark'];
        $upManager = new UploadManager();
        $auth      = new Auth($accessKey, $secretKey);
        $token     = $auth->uploadToken($this->config['bucket']);

        $result = $upManager->putFile($token, $file, $filePath);

        $previewUrl = $fileType == 'image' ? $this->getPreviewUrl($file, $watermark) : $this->getFileDownloadUrl($file);
        $url        = $fileType == 'image' ? $this->getImageUrl($file, $watermark) : $this->getFileDownloadUrl($file);

        return [
            'preview_url' => $previewUrl,
            'url'         => $url,
        ];
    }

    /**
     * 获取图片预览地址
     * @param string $file
     * @param string $style
     * @return mixed
     */
    public function getPreviewUrl($file, $style = 'watermark')
    {
        $url = $this->getUrl($file, $style);

        return $url;
    }

    /**
     * 获取图片地址
     * @param string $file
     * @param string $style
     * @return mixed
     */
    public function getImageUrl($file, $style = 'watermark')
    {
        $config = $this->config;
        $url    = $this->storageRoot . $file;

        if (!empty($style)) {
            $url = $url . $config['style_separator'] . $style;
        }

        return $url;
    }

    /**
     * 获取文件地址
     * @param string $file
     * @param string $style
     * @return mixed
     */
    public function getUrl($file, $style = '')
    {
        $config = $this->config;
        $url    = $this->storageRoot . $file;

        if (!empty($style)) {
            $url = $url . $config['style_separator'] . $style;
        }

        return $url;
    }

    /**
     * 获取文件下载地址
     * @param string $file
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

    /**
     * 获取云存储域名
     * @return mixed
     */
    public function getDomain()
    {
        return $this->config['domain'];
    }

    /**
     * 获取文件相对上传目录路径
     * @param string $url
     * @return mixed
     */
    public function getFilePath($url)
    {
        $parsedUrl = parse_url($url);

        if (!empty($parsedUrl['path'])) {
            $url            = ltrim($parsedUrl['path'], '/\\');
            $config         = $this->config;
            $styleSeparator = $config['style_separator'];

            $styleSeparatorPosition = strpos($url, $styleSeparator);
            if ($styleSeparatorPosition !== false) {
                $url = substr($url, 0, strpos($url, $styleSeparator));
            }
        } else {
            $url = '';
        }

        return $url;
    }
}