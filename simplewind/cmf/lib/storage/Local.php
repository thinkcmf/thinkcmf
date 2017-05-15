<?php

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
     * @param $file
     * @param string $filePath
     * @return array
     */
    public function upload($file, $filePath = '')
    {
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
        return cmf_get_root() . '/upload/' . $file;
    }

    /**
     * @param $file
     * @param string $style
     * @return string
     */
    public function getUrl($file, $style = '')
    {
        return cmf_get_root() . '/upload/' . $file;
    }

    /**
     * @param $file
     * @param int $expires
     * @return mixed
     */
    public function getFileDownloadUrl($file, $expires = 3600)
    {
        $url = $this->getUrl($file);
        return $url;
    }

    public function getDomain()
    {
        return request()->host();
    }

    public function getFilePath($url)
    {
        $storageDomain = $this->getDomain();
        $url           = preg_replace("/^http(s)?:\/\/$storageDomain\//", '', $url);
        return $url;
    }
}