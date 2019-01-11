<?php
namespace Swoole\Coroutine;

class Exception extends \Swoole\Exception
{

    protected $message;
    protected $code;
    protected $file;
    protected $line;
    protected $cid;
    protected $originCid;
    protected $originFile;
    protected $originLine;
    protected $originTrace;

    /**
     * @return mixed
     */
    public function getCid(){}

    /**
     * @return mixed
     */
    public function getOriginCid(){}

    /**
     * @return mixed
     */
    public function getOriginFile(){}

    /**
     * @return mixed
     */
    public function getOriginLine(){}

    /**
     * @return mixed
     */
    public function getOriginTrace(){}

    /**
     * @param $message[optional]
     * @param $code[optional]
     * @param $previous[optional]
     * @return mixed
     */
    public function __construct($message=null, $code=null, $previous=null){}

    /**
     * @return mixed
     */
    public function __wakeup(){}

    /**
     * @return mixed
     */
    public function __toString(){}


}
