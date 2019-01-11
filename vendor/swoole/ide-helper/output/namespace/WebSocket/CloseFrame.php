<?php
namespace Swoole\WebSocket;

class CloseFrame extends \Swoole\WebSocket\Frame
{

    public $opcode;
    public $code;
    public $reason;

    /**
     * @return mixed
     */
    public function __toString(){}

    /**
     * @param $data[required]
     * @param $opcode[optional]
     * @param $finish[optional]
     * @param $mask[optional]
     * @return mixed
     */
    public static function pack($data, $opcode = null, $finish = null, $mask = null){}

    /**
     * @param $data[required]
     * @return mixed
     */
    public static function unpack($data){}


}
