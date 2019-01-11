<?php
namespace Swoole\Server;

class Task
{

    public $data;
    public $id;
    public $worker_id;
    public $flags;

    /**
     * @param $data[required]
     * @return mixed
     */
    public function finish($data){}


}
