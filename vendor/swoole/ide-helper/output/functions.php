<?php
/**
 * @since 4.2.13-alpha
 */

function swoole_version(){}

function swoole_cpu_num(){}

function swoole_last_error(){}

/**
 * @param $fd[required]
 * @param $read_callback[required]
 * @param $write_callback[optional]
 * @param $events[optional]
 * @return mixed
 */
function swoole_event_add($fd, $read_callback, $write_callback = null, $events = null){}

/**
 * @param $fd[required]
 * @param $read_callback[optional]
 * @param $write_callback[optional]
 * @param $events[optional]
 * @return mixed
 */
function swoole_event_set($fd, $read_callback = null, $write_callback = null, $events = null){}

/**
 * @param $fd[required]
 * @return mixed
 */
function swoole_event_del($fd){}

function swoole_event_exit(){}

function swoole_event_wait(){}

/**
 * @param $fd[required]
 * @param $data[required]
 * @return mixed
 */
function swoole_event_write($fd, $data){}

/**
 * @param $callback[required]
 * @return mixed
 */
function swoole_event_defer($callback){}

/**
 * @param $callback[required]
 * @param $before[optional]
 * @return mixed
 */
function swoole_event_cycle($callback, $before = null){}

function swoole_event_dispatch(){}

/**
 * @param $fd[required]
 * @param $events[optional]
 * @return mixed
 */
function swoole_event_isset($fd, $events = null){}

/**
 * @param $ms[required]
 * @param $callback[required]
 * @param $param[optional]
 * @return mixed
 */
function swoole_timer_after($ms, $callback, $param = null){}

/**
 * @param $ms[required]
 * @param $callback[required]
 * @return mixed
 */
function swoole_timer_tick($ms, $callback){}

/**
 * @param $timer_id[required]
 * @return mixed
 */
function swoole_timer_exists($timer_id){}

/**
 * @param $timer_id[required]
 * @return mixed
 */
function swoole_timer_clear($timer_id){}

/**
 * @param $settings[required]
 * @return mixed
 */
function swoole_async_set($settings){}

/**
 * @param $filename[required]
 * @param $callback[required]
 * @param $chunk_size[optional]
 * @param $offset[optional]
 * @return mixed
 */
function swoole_async_read($filename, $callback, $chunk_size = null, $offset = null){}

/**
 * @param $filename[required]
 * @param $content[required]
 * @param $offset[optional]
 * @param $callback[optional]
 * @return mixed
 */
function swoole_async_write($filename, $content, $offset = null, $callback = null){}

/**
 * @param $filename[required]
 * @param $callback[required]
 * @return mixed
 */
function swoole_async_readfile($filename, $callback){}

/**
 * @param $filename[required]
 * @param $content[required]
 * @param $callback[optional]
 * @param $flags[optional]
 * @return mixed
 */
function swoole_async_writefile($filename, $content, $callback = null, $flags = null){}

/**
 * @param $hostname[required]
 * @param $callback[required]
 * @return mixed
 */
function swoole_async_dns_lookup($hostname, $callback){}

/**
 * @param $domain_name[required]
 * @param $timeout[optional]
 * @return mixed
 */
function swoole_async_dns_lookup_coro($domain_name, $timeout = null){}

/**
 * @param $func[required]
 * @return mixed
 */
function swoole_coroutine_create($func){}

/**
 * @param $command[required]
 * @return mixed
 */
function swoole_coroutine_exec($command){}

/**
 * @param $callback[required]
 * @return mixed
 */
function swoole_coroutine_defer($callback){}

/**
 * @param $func[required]
 * @return mixed
 */
function go($func){}

/**
 * @param $callback[required]
 * @return mixed
 */
function defer($callback){}

/**
 * @param $read_array[required]
 * @param $write_array[required]
 * @param $error_array[required]
 * @param $timeout[optional]
 * @return mixed
 */
function swoole_client_select($read_array, $write_array, $error_array, $timeout = null){}

/**
 * @param $read_array[required]
 * @param $write_array[required]
 * @param $error_array[required]
 * @param $timeout[optional]
 * @return mixed
 */
function swoole_select($read_array, $write_array, $error_array, $timeout = null){}

/**
 * @param $process_name[required]
 * @return mixed
 */
function swoole_set_process_name($process_name){}

function swoole_get_local_ip(){}

function swoole_get_local_mac(){}

/**
 * @param $errno[required]
 * @param $error_type[optional]
 * @return mixed
 */
function swoole_strerror($errno, $error_type = null){}

function swoole_errno(){}

/**
 * @param $data[required]
 * @param $type[optional]
 * @return mixed
 */
function swoole_hashcode($data, $type = null){}

/**
 * @param $filename[required]
 * @return mixed
 */
function swoole_get_mime_type($filename){}

function swoole_call_user_shutdown_begin(){}

function swoole_clear_dns_cache(){}

