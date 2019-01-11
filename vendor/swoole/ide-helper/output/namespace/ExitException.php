<?php
namespace Swoole;

class ExitException extends \Swoole\Exception
{

    protected $message;
    protected $code;
    protected $file;
    protected $line;

    /**
     * @return mixed
     */
    public function getFlags(){}

    /**
     * @return mixed
     */
    public function getStatus(){}

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
