<?php
namespace Swoole;

class Process
{
    const IPC_NOWAIT = 256;
    const PIPE_MASTER = 1;
    const PIPE_WORKER = 2;
    const PIPE_READ = 3;
    const PIPE_WRITE = 4;

    public $pipe;
    public $callback;
    public $msgQueueId;
    public $msgQueueKey;
    public $pid;
    public $id;

    /**
     * @param $callback[required]
     * @param $redirect_stdin_and_stdout[optional]
     * @param $pipe_type[optional]
     * @return mixed
     */
    public function __construct($callback, $redirect_stdin_and_stdout = null, $pipe_type = null){}

    /**
     * @return mixed
     */
    public function __destruct(){}

    /**
     * @param $blocking[optional]
     * @return mixed
     */
    public static function wait($blocking = null){}

    /**
     * @param $signal_no[required]
     * @param $callback[required]
     * @return mixed
     */
    public static function signal($signal_no, $callback){}

    /**
     * @param $usec[required]
     * @return mixed
     */
    public static function alarm($usec){}

    /**
     * @param $pid[required]
     * @param $signal_no[optional]
     * @return mixed
     */
    public static function kill($pid, $signal_no = null){}

    /**
     * @param $nochdir[optional]
     * @param $noclose[optional]
     * @return mixed
     */
    public static function daemon($nochdir = null, $noclose = null){}

    /**
     * @param $seconds[required]
     * @return mixed
     */
    public function setTimeout($seconds){}

    /**
     * @param $blocking[required]
     * @return mixed
     */
    public function setBlocking($blocking){}

    /**
     * @param $key[optional]
     * @param $mode[optional]
     * @param $capacity[optional]
     * @return mixed
     */
    public function useQueue($key = null, $mode = null, $capacity = null){}

    /**
     * @return mixed
     */
    public function statQueue(){}

    /**
     * @return mixed
     */
    public function freeQueue(){}

    /**
     * @return mixed
     */
    public function start(){}

    /**
     * @param $data[required]
     * @return mixed
     */
    public function write($data){}

    /**
     * @return mixed
     */
    public function close(){}

    /**
     * @param $size[optional]
     * @return mixed
     */
    public function read($size = null){}

    /**
     * @param $data[required]
     * @return mixed
     */
    public function push($data){}

    /**
     * @param $size[optional]
     * @return mixed
     */
    public function pop($size = null){}

    /**
     * @param $exit_code[optional]
     * @return mixed
     */
    public function exit($exit_code = null){}

    /**
     * @param $exec_file[required]
     * @param $args[required]
     * @return mixed
     */
    public function exec($exec_file, $args){}

    /**
     * @param $process_name[required]
     * @return mixed
     */
    public function name($process_name){}


}
