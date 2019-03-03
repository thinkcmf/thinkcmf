<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace cmf\lib\storage;

class Local
{
    private $config;

    /**
     * Local constructor.
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * 文件上传
     * @param string $file 上传文件路径
     * @param string $filePath 文件路径相对于upload目录
     * @param string $fileType 文件类型,image,video,audio,file
     * @param array $param 额外参数
     * @return mixed
     */
    public function upload($file, $filePath = '', $fileType = 'image', $param = null)
    {
        return [
            'preview_url' => $this->getPreviewUrl($file),
            'url'         => $this->getUrl($file),
        ];
    }

    /**
     * 获取图片地址
     * @param string $file
     * @param string $style
     * @return mixed
     */
    public function getImageUrl($file, $style = '')
    {
        return $this->_getWebRoot() . '/upload/' . $file;
    }

    /**
     * 获取图片预览地址
     * @param string $file
     * @param string $style
     * @return mixed
     */
    public function getPreviewUrl($file, $style = '')
    {
        return $this->_getWebRoot() . '/upload/' . $file;
    }

    /**
     * 获取文件地址
     * @param string $file
     * @param string $style
     * @return mixed
     */
    public function getUrl($file, $style = '')
    {
        return $this->_getWebRoot() . '/upload/' . $file;
    }

    /**
     * 获取文件下载地址
     * @param string $file
     * @param int $expires
     * @return mixed
     */
    public function getFileDownloadUrl($file, $expires = 3600)
    {
        $url = $this->getUrl($file);
        return $url;
    }

    /**
     * 获取本地存储域名
     * @return mixed
     */
    public function getDomain()
    {
        return request()->host();
    }

    /**
     * 获取文件相对上传目录路径
     * @param string $url
     * @return mixed
     */
    public function getFilePath($url)
    {
        $storageDomain = $this->getDomain();
        $url           = preg_replace("/^http(s)?:\/\/$storageDomain/", '', $url);
        $url           = preg_replace("/^\/upload\//", '', $url);
        return $url;
    }

    private function _getWebRoot()
    {
        return cmf_get_domain() . cmf_get_root();

    }
}