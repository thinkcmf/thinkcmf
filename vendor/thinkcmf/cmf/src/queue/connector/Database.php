<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: 老猫 <catmat@thinkcmf.com>
// +----------------------------------------------------------------------

/*
 * 数据库表
CREATE TABLE `cmf_queue_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved` tinyint(3) unsigned NOT NULL,
  `reserve_time` int(10) unsigned DEFAULT NULL,
  `available_time` int(10) unsigned NOT NULL,
  `create_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
 */

namespace cmf\queue\connector;

use think\queue\connector\Database as DataBaseConnector;

class Database extends DataBaseConnector
{
    protected $options = [
        'expire'  => 60,
        'default' => 'default',
        'table'   => 'queue_jobs',
        'dsn'     => []
    ];

    /**
     * Push a raw payload to the database with a given delay.
     *
     * @param  \DateTime|int $delay
     * @param  string|null $queue
     * @param  string $payload
     * @param  int $attempts
     * @return mixed
     */
    protected function pushToDatabase($delay, $queue, $payload, $attempts = 0)
    {
        return $this->db->name($this->options['table'])->insert([
            'queue'          => $this->getQueue($queue),
            'payload'        => $payload,
            'attempts'       => $attempts,
            'reserved'       => 0,
            'reserve_time'   => null,
            'available_time' => time() + $delay,
            'create_time'    => time()
        ]);
    }

    /**
     * 获取下个有效任务
     *
     * @param  string|null $queue
     * @return \StdClass|null
     */
    protected function getNextAvailableJob($queue)
    {
        $this->db->startTrans();

        $job = $this->db->name($this->options['table'])
            ->lock(true)
            ->where('queue', $this->getQueue($queue))
            ->where('reserved', 0)
            ->where('available_time', '<=', time())
            ->order('id', 'asc')
            ->find();

        return $job ? (object)$job : null;
    }

    /**
     * 标记任务正在执行.
     *
     * @param  string $id
     * @return void
     */
    protected function markJobAsReserved($id)
    {
        $this->db->name($this->options['table'])->where('id', $id)->update([
            'reserved'     => 1,
            'reserve_time' => time()
        ]);
    }

    /**
     * 重新发布超时的任务
     *
     * @param  string $queue
     * @return void
     */
    protected function releaseJobsThatHaveBeenReservedTooLong($queue)
    {
        $expired = time() - $this->options['expire'];

        $this->db->name($this->options['table'])
            ->where('queue', $this->getQueue($queue))
            ->where('reserved', 1)
            ->where('reserve_time', '<=', $expired)
            ->update([
                'reserved'     => 0,
                'reserve_time' => null,
                'attempts'     => $this->db->raw('attempts + 1')
            ]);
    }


}
