<?php
namespace Swoole;

class Exception extends \Exception
{

    protected $message;
    protected $code;
    protected $file;
    protected $line;

    /**
     * @param $message[optional]
     * @param $code[optional]
     * @param $previous[optional]
     * @return mixed
     */
    public function __construct($message = null, $code = null, $previous = null){}

    /**
     * @return mixed
     */
    public function __wakeup(){}

    /**
     * @return mixed
     */
    public function __toString(){}


}
