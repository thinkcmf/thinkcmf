<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace think\swoole;

use Swoole\Table;
use think\Container;
use think\facade\Cache;
use think\facade\Cookie as ThinkCookie;
use think\Session as BaseSession;

/**
 * Swoole Cookie类
 */
class Session extends BaseSession
{
    /**
     * Session数据
     * @var array
     */
    protected $data = [];

    /**
     * 记录Session name
     * @var string
     */
    protected $sessionName = 'PHPSESSID';

    /**
     * Session有效期
     * @var int
     */
    protected $expire = 0;

    /**
     * Swoole_table对象
     * @var Table
     */
    protected $swooleTable;

    /**
     * session初始化
     * @access public
     * @param  array $config
     * @return void
     * @throws \think\Exception
     */
    public function init(array $config = [])
    {
        $config = $config ?: $this->config;

        if (!empty($config['name'])) {
            $this->sessionName = $config['name'];
        }

        if (!empty($config['expire'])) {
            $this->expire = $config['expire'];
        }

        if (!empty($config['auto_start'])) {
            $this->start();
        } else {
            $this->init = false;
        }

        return $this;
    }

    /**
     * session自动启动或者初始化
     * @access public
     * @return void
     */
    public function boot()
    {
        if (is_null($this->init)) {
            $this->init();
        }

        if (false === $this->init) {
            $this->start();
        }
    }

    public function name($name)
    {
        $this->sessionName = $name;
    }

    /**
     * session_id设置
     * @access public
     * @param  string     $id session_id
     * @param  int        $expire Session有效期
     * @return void
     */
    public function setId($id, $expire = null)
    {
        ThinkCookie::set($this->sessionName, $id, $expire);
    }

    /**
     * 获取session_id
     * @access public
     * @param  bool        $regenerate 不存在是否自动生成
     * @return string
     */
    public function getId($regenerate = true)
    {
        $sessionId = ThinkCookie::get($this->sessionName) ?: '';

        if (!$sessionId && $regenerate) {
            $sessionId = $this->regenerate();
        }
        
        return $sessionId;
    }

    /**
     * session设置
     * @access public
     * @param  string        $name session名称
     * @param  mixed         $value session值
     * @return void
     */
    public function set($name, $value, $prefix = null)
    {
        empty($this->init) && $this->boot();

        $sessionId = $this->getId();

        $this->setSession($sessionId, $name, $value);
    }

    /**
     * session设置
     * @access protected
     * @param  string        $sessionId session_id
     * @param  string        $name session名称
     * @param  mixed         $value session值
     * @param  string|null   $prefix 作用域（前缀）
     * @return void
     */
    protected function setSession($sessionId, $name, $value)
    {
        if (strpos($name, '.')) {
            // 二维数组赋值
            list($name1, $name2) = explode('.', $name);

            $this->data[$sessionId][$name1][$name2] = $value;
        } else {
            $this->data[$sessionId][$name] = $value;
        }

        // 持久化session数据
        $this->writeSessionData($sessionId);
    }

    /**
     * session获取
     * @access public
     * @param  string        $name session名称
     * @return mixed
     */
    public function get($name = '', $prefix = null)
    {
        empty($this->init) && $this->boot();

        $sessionId = $this->getId();

        return $this->readSession($sessionId, $name);
    }

    /**
     * session获取
     * @access protected
     * @param  string        $sessionId session_id
     * @param  string        $name session名称
     * @return mixed
     */
    protected function readSession($sessionId, $name = '')
    {
        $value = isset($this->data[$sessionId]) ? $this->data[$sessionId] : [];

        if (!is_array($value)) {
            $value = [];
        }

        if ('' != $name) {
            $name = explode('.', $name);

            foreach ($name as $val) {
                if (isset($value[$val])) {
                    $value = $value[$val];
                } else {
                    $value = null;
                    break;
                }
            }
        }

        return $value;
    }

    /**
     * 删除session数据
     * @access public
     * @param  string|array  $name session名称
     * @return void
     */
    public function delete($name, $prefix = null)
    {
        empty($this->init) && $this->boot();

        $sessionId = $this->getId(false);

        if ($sessionId) {
            $this->deleteSession($sessionId, $name);

            // 持久化session数据
            $this->writeSessionData($sessionId);
        }
    }

    /**
     * 删除session数据
     * @access protected
     * @param  string        $sessionId session_id
     * @param  string|array  $name session名称
     * @return void
     */
    protected function deleteSession($sessionId, $name)
    {
        if (is_array($name)) {
            foreach ($name as $key) {
                $this->deleteSession($sessionId, $key);
            }
        } elseif (strpos($name, '.')) {
            list($name1, $name2) = explode('.', $name);
            unset($this->data[$sessionId][$name1][$name2]);
        } else {
            unset($this->data[$sessionId][$name]);
        }
    }

    protected function writeSessionData($sessionId)
    {
        if ($this->swooleTable) {
            $this->swooleTable->set('sess_' . $sessionId, [
                'data'   => json_encode($this->data[$sessionId]),
                'expire' => time() + $this->expire,
            ]);
        } else {
            Cache::set('sess_' . $sessionId, $this->data[$sessionId], $this->expire);
        }
    }

    /**
     * 清空session数据
     * @access public
     * @return void
     */
    public function clear($prefix = null)
    {
        empty($this->init) && $this->boot();

        $sessionId = $this->getId(false);

        if ($sessionId) {
            $this->clearSession($sessionId);
        }
    }

    /**
     * 清空session数据
     * @access protected
     * @param  string        $sessionId session_id
     * @return void
     */
    protected function clearSession($sessionId)
    {
        $this->data[$sessionId] = [];

        if ($this->swooleTable) {
            $this->swooleTable->del('sess_' . $sessionId);
        } else {
            Cache::rm('sess_' . $sessionId);
        }
    }

    /**
     * 判断session数据
     * @access public
     * @param  string        $name session名称
     * @return bool
     */
    public function has($name, $prefix = null)
    {
        empty($this->init) && $this->boot();

        $sessionId = $this->getId(false);

        if ($sessionId) {
            return $this->hasSession($sessionId, $name);
        }

        return false;
    }

    /**
     * 判断session数据
     * @access protected
     * @param  string        $sessionId session_id
     * @param  string        $name session名称
     * @return bool
     */
    protected function hasSession($sessionId, $name)
    {
        $value = isset($this->data[$sessionId]) ? $this->data[$sessionId] : [];

        $name = explode('.', $name);

        foreach ($name as $val) {
            if (!isset($value[$val])) {
                return false;
            } else {
                $value = $value[$val];
            }
        }

        return true;
    }

    /**
     * 启动session
     * @access public
     * @return void
     */
    public function start()
    {
        $sessionId = $this->getId();

        // 读取缓存数据
        if (empty($this->data[$sessionId])) {
            if (!empty($this->config['use_swoole_table'])) {
                $this->swooleTable = Container::get('swoole_table');

                $result = $this->swooleTable->get('sess_' . $sessionId);

                if (0 == $result['expire'] || time() <= $result['expire']) {
                    $data = $result['data'];
                }
            } else {
                $data = Cache::get('sess_' . $sessionId);
            }

            if (!empty($data)) {
                $this->data[$sessionId] = $data;
            }
        }

        $this->init = true;
    }

    /**
     * 销毁session
     * @access public
     * @return void
     */
    public function destroy()
    {
        $sessionId = $this->getId(false);

        if ($sessionId) {
            $this->destroySession($sessionId);
        }

        $this->init = null;
    }

    /**
     * 销毁session
     * @access protected
     * @param  string        $sessionId session_id
     * @return void
     */
    protected function destroySession($sessionId)
    {
        if (isset($this->data[$sessionId])) {
            unset($this->data[$sessionId]);

            if ($this->swooleTable) {
                $this->swooleTable->del('sess_' . $sessionId);
            } else {
                Cache::rm('sess_' . $sessionId);
            }
        }
    }

    /**
     * 生成session_id
     * @access public
     * @param  bool $delete 是否删除关联会话文件
     * @return string
     */
    public function regenerate($delete = false)
    {
        if ($delete) {
            $this->destroy();
        }

        $sessionId = md5(microtime(true) . uniqid());

        $this->setId($sessionId);

        return $sessionId;
    }

    /**
     * 暂停session
     * @access public
     * @return void
     */
    public function pause()
    {
        $this->init = false;
    }
}
