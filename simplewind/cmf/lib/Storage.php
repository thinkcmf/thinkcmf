<?php

namespace cmf\lib;

class Storage
{

    private $driver;

    /**
     * @var object 对象实例
     */
    protected static $instance;

    /**
     * 构造方法，用于构造存储实例
     * @param string $driver 要使用的存储驱动 Qiniu-七牛存储驱动
     * @param array $driverConfig
     * @throws \Exception
     */
    public function __construct($driver = null, $driverConfig = null)
    {
        if (empty($driver)) {
            $storageSetting = cmf_get_option('storage');

            if (empty($storageSetting)) {
                $driver       = 'Local';
                $driverConfig = [];
            } else {
                $driver = $storageSetting['type'];
                if ($driver == 'Local') {
                    $driverConfig = [];
                } else {
                    $driverConfig = $storageSetting[$driver];
                }

            }

        }

        if (empty($driverConfig['driver'])) {
            $storageDriverClass = "\\cmf\\lib\\storage\\$driver";

            $storage = new $storageDriverClass($driverConfig);

            $this->driver = $storage;
        }
    }

    /**
     * @param $file
     * @param $filePath
     * @return array
     */
    public function upload($file, $filePath)
    {
        return $this->driver->upload($file, $filePath);
    }

    /**
     * 初始化
     * @param $type
     * @param $config
     * @return \cmf\lib\Storage
     */
    public static function instance($type = null, $config = null)
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($type, $config);
        }
        return self::$instance;
    }

    /**
     * @param $file
     * @param string $style
     * @return mixed
     */
    public function getPreviewUrl($file, $style = '')
    {
        return $this->driver->getPreviewUrl($file, $style);
    }

    /**
     * @param $file
     * @param string $style
     * @return mixed
     */
    public function getUrl($file, $style = '')
    {
        return $this->driver->getUrl($file, $style);
    }

    /**
     * @param $file
     * @param int $expires
     * @return mixed
     */
    public function getFileDownloadUrl($file, $expires = 3600)
    {
        return $this->driver->getFileDownloadUrl($file, $expires);
    }


}