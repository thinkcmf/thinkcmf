<?php
/**
 * Created by PhpStorm.
 * User: xavier
 * Date: 2018/9/1
 * Time: 上午11:12
 * Email:499873958@qq.com
 */

namespace think\swoole;

use think\facade\Config;

/**
 * 基于Swoole_table的高速缓存
 * Class Cache
 * @package think\swoole
 */
class CacheTable
{
    private $table;

    public function __construct()
    {
        $cache_size      = Config::get('swoole.cache_size');
        $cache_data_size = Config::get('swoole.cache_data_size');
        $cache_data_size = $cache_data_size ? $cache_data_size : 1024;
        $cache_size      = $cache_size ? $cache_size : 1024 * 1024;

        $this->table = new \swoole_table($cache_size);
        $this->table->column('time', \swoole_table::TYPE_INT, 15);
        $this->table->column('data', \swoole_table::TYPE_STRING, $cache_data_size);
        $this->table->create();
    }

    public function getTable()
    {
        return $this->table;
    }

    public function set($key, $value)
    {
        $this->table->set($key, ['time' => 0, 'data' => $value]);
    }

    public function setex($key, $expire, $value)
    {
        $this->table->set($key, ['time' => time() + $expire, 'data' => $value]);
    }

    public function incr($key, $column, $incrby = 1)
    {
        $this->table->incr($key, $column, $incrby);
    }

    public function decr($key, $column, $decrby = 1)
    {
        $this->table->decr($key, $column, $decrby);
    }

    public function get($key, $field = null)
    {
        $data = $this->table->get($key, $field);
        if (false == $data) {
            return $data;
        }
        if (0 == $data['time']) {
            return $data['data'];
        }
        if (0 <= $data['time'] && $data['time'] < time()) {
            $this->del($key);
            return false;
        }
        return $data['data'];
    }

    public function exist($key)
    {
        return $this->table->exist($key);
    }

    public function del($key)
    {
        return $this->table->del($key);
    }

    public function clear()
    {
        foreach ($this->table as $key => $val) {
            $this->del($key);
        }
    }
}
