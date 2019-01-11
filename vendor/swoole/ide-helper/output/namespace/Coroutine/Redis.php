<?php
namespace Swoole\Coroutine;

class Redis
{

    public $host;
    public $port;
    public $setting;
    public $sock;
    public $connected;
    public $errType;
    public $errCode;
    public $errMsg;

    /**
     * @param $config[optional]
     * @return mixed
     */
    public function __construct($config = null){}

    /**
     * @return mixed
     */
    public function __destruct(){}

    /**
     * @param $host[required]
     * @param $port[optional]
     * @param $serialize[optional]
     * @return mixed
     */
    public function connect($host, $port = null, $serialize = null){}

    /**
     * @return mixed
     */
    public function getOptions(){}

    /**
     * @param $options[required]
     * @return mixed
     */
    public function setOptions($options){}

    /**
     * @return mixed
     */
    public function setDefer(){}

    /**
     * @return mixed
     */
    public function getDefer(){}

    /**
     * @return mixed
     */
    public function recv(){}

    /**
     * @param $params[required]
     * @return mixed
     */
    public function request($params){}

    /**
     * @return mixed
     */
    public function close(){}

    /**
     * @param $key[required]
     * @param $value[required]
     * @param $timeout[optional]
     * @param $opt[optional]
     * @return mixed
     */
    public function set($key, $value, $timeout = null, $opt = null){}

    /**
     * @param $key[required]
     * @param $offset[required]
     * @param $value[required]
     * @return mixed
     */
    public function setBit($key, $offset, $value){}

    /**
     * @param $key[required]
     * @param $expire[required]
     * @param $value[required]
     * @return mixed
     */
    public function setEx($key, $expire, $value){}

    /**
     * @param $key[required]
     * @param $expire[required]
     * @param $value[required]
     * @return mixed
     */
    public function psetEx($key, $expire, $value){}

    /**
     * @param $key[required]
     * @param $index[required]
     * @param $value[required]
     * @return mixed
     */
    public function lSet($key, $index, $value){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function get($key){}

    /**
     * @param $keys[required]
     * @return mixed
     */
    public function mGet($keys){}

    /**
     * @param $key[required]
     * @param $other_keys[optional]
     * @return mixed
     */
    public function del($key, $other_keys = null){}

    /**
     * @param $key[required]
     * @param $member[required]
     * @param $other_members[optional]
     * @return mixed
     */
    public function hDel($key, $member, $other_members = null){}

    /**
     * @param $key[required]
     * @param $member[required]
     * @param $value[required]
     * @return mixed
     */
    public function hSet($key, $member, $value){}

    /**
     * @param $key[required]
     * @param $pairs[required]
     * @return mixed
     */
    public function hMSet($key, $pairs){}

    /**
     * @param $key[required]
     * @param $member[required]
     * @param $value[required]
     * @return mixed
     */
    public function hSetNx($key, $member, $value){}

    /**
     * @param $key[required]
     * @param $other_keys[optional]
     * @return mixed
     */
    public function delete($key, $other_keys = null){}

    /**
     * @param $pairs[required]
     * @return mixed
     */
    public function mSet($pairs){}

    /**
     * @param $pairs[required]
     * @return mixed
     */
    public function mSetNx($pairs){}

    /**
     * @param $pattern[required]
     * @return mixed
     */
    public function getKeys($pattern){}

    /**
     * @param $pattern[required]
     * @return mixed
     */
    public function keys($pattern){}

    /**
     * @param $key[required]
     * @param $other_keys[optional]
     * @return mixed
     */
    public function exists($key, $other_keys = null){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function type($key){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function strLen($key){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function lPop($key){}

    /**
     * @param $key[required]
     * @param $timeout_or_key[required]
     * @param $extra_args[optional]
     * @return mixed
     */
    public function blPop($key, $timeout_or_key, $extra_args = null){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function rPop($key){}

    /**
     * @param $key[required]
     * @param $timeout_or_key[required]
     * @param $extra_args[optional]
     * @return mixed
     */
    public function brPop($key, $timeout_or_key, $extra_args = null){}

    /**
     * @param $src[required]
     * @param $dst[required]
     * @param $timeout[required]
     * @return mixed
     */
    public function bRPopLPush($src, $dst, $timeout){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function lSize($key){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function lLen($key){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function sSize($key){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function scard($key){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function sPop($key){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function sMembers($key){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function sGetMembers($key){}

    /**
     * @param $key[required]
     * @param $count[optional]
     * @return mixed
     */
    public function sRandMember($key, $count = null){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function persist($key){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function ttl($key){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function pttl($key){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function zCard($key){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function zSize($key){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function hLen($key){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function hKeys($key){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function hVals($key){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function hGetAll($key){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function debug($key){}

    /**
     * @param $ttl[required]
     * @param $key[required]
     * @param $value[required]
     * @return mixed
     */
    public function restore($ttl, $key, $value){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function dump($key){}

    /**
     * @param $key[required]
     * @param $newkey[required]
     * @return mixed
     */
    public function renameKey($key, $newkey){}

    /**
     * @param $key[required]
     * @param $newkey[required]
     * @return mixed
     */
    public function rename($key, $newkey){}

    /**
     * @param $key[required]
     * @param $newkey[required]
     * @return mixed
     */
    public function renameNx($key, $newkey){}

    /**
     * @param $src[required]
     * @param $dst[required]
     * @return mixed
     */
    public function rpoplpush($src, $dst){}

    /**
     * @return mixed
     */
    public function randomKey(){}

    /**
     * @param $key[required]
     * @param $elements[required]
     * @return mixed
     */
    public function pfadd($key, $elements){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function pfcount($key){}

    /**
     * @param $dstkey[required]
     * @param $keys[required]
     * @return mixed
     */
    public function pfmerge($dstkey, $keys){}

    /**
     * @return mixed
     */
    public function ping(){}

    /**
     * @param $password[required]
     * @return mixed
     */
    public function auth($password){}

    /**
     * @return mixed
     */
    public function unwatch(){}

    /**
     * @param $key[required]
     * @param $other_keys[optional]
     * @return mixed
     */
    public function watch($key, $other_keys = null){}

    /**
     * @return mixed
     */
    public function save(){}

    /**
     * @return mixed
     */
    public function bgSave(){}

    /**
     * @return mixed
     */
    public function lastSave(){}

    /**
     * @return mixed
     */
    public function flushDB(){}

    /**
     * @return mixed
     */
    public function flushAll(){}

    /**
     * @return mixed
     */
    public function dbSize(){}

    /**
     * @return mixed
     */
    public function bgrewriteaof(){}

    /**
     * @return mixed
     */
    public function time(){}

    /**
     * @return mixed
     */
    public function role(){}

    /**
     * @param $key[required]
     * @param $offset[required]
     * @param $value[required]
     * @return mixed
     */
    public function setRange($key, $offset, $value){}

    /**
     * @param $key[required]
     * @param $value[required]
     * @return mixed
     */
    public function setNx($key, $value){}

    /**
     * @param $key[required]
     * @param $value[required]
     * @return mixed
     */
    public function getSet($key, $value){}

    /**
     * @param $key[required]
     * @param $value[required]
     * @return mixed
     */
    public function append($key, $value){}

    /**
     * @param $key[required]
     * @param $value[required]
     * @return mixed
     */
    public function lPushx($key, $value){}

    /**
     * @param $key[required]
     * @param $value[required]
     * @return mixed
     */
    public function lPush($key, $value){}

    /**
     * @param $key[required]
     * @param $value[required]
     * @return mixed
     */
    public function rPush($key, $value){}

    /**
     * @param $key[required]
     * @param $value[required]
     * @return mixed
     */
    public function rPushx($key, $value){}

    /**
     * @param $key[required]
     * @param $value[required]
     * @return mixed
     */
    public function sContains($key, $value){}

    /**
     * @param $key[required]
     * @param $value[required]
     * @return mixed
     */
    public function sismember($key, $value){}

    /**
     * @param $key[required]
     * @param $member[required]
     * @return mixed
     */
    public function zScore($key, $member){}

    /**
     * @param $key[required]
     * @param $member[required]
     * @return mixed
     */
    public function zRank($key, $member){}

    /**
     * @param $key[required]
     * @param $member[required]
     * @return mixed
     */
    public function zRevRank($key, $member){}

    /**
     * @param $key[required]
     * @param $member[required]
     * @return mixed
     */
    public function hGet($key, $member){}

    /**
     * @param $key[required]
     * @param $keys[required]
     * @return mixed
     */
    public function hMGet($key, $keys){}

    /**
     * @param $key[required]
     * @param $member[required]
     * @return mixed
     */
    public function hExists($key, $member){}

    /**
     * @param $channel[required]
     * @param $message[required]
     * @return mixed
     */
    public function publish($channel, $message){}

    /**
     * @param $key[required]
     * @param $value[required]
     * @param $member[required]
     * @return mixed
     */
    public function zIncrBy($key, $value, $member){}

    /**
     * @param $key[required]
     * @param $score[required]
     * @param $value[required]
     * @return mixed
     */
    public function zAdd($key, $score, $value){}

    /**
     * @param $key[required]
     * @param $min[required]
     * @param $max[required]
     * @return mixed
     */
    public function zDeleteRangeByScore($key, $min, $max){}

    /**
     * @param $key[required]
     * @param $min[required]
     * @param $max[required]
     * @return mixed
     */
    public function zRemRangeByScore($key, $min, $max){}

    /**
     * @param $key[required]
     * @param $min[required]
     * @param $max[required]
     * @return mixed
     */
    public function zCount($key, $min, $max){}

    /**
     * @param $key[required]
     * @param $start[required]
     * @param $end[required]
     * @param $scores[optional]
     * @return mixed
     */
    public function zRange($key, $start, $end, $scores = null){}

    /**
     * @param $key[required]
     * @param $start[required]
     * @param $end[required]
     * @param $scores[optional]
     * @return mixed
     */
    public function zRevRange($key, $start, $end, $scores = null){}

    /**
     * @param $key[required]
     * @param $start[required]
     * @param $end[required]
     * @param $options[optional]
     * @return mixed
     */
    public function zRangeByScore($key, $start, $end, $options = null){}

    /**
     * @param $key[required]
     * @param $start[required]
     * @param $end[required]
     * @param $options[optional]
     * @return mixed
     */
    public function zRevRangeByScore($key, $start, $end, $options = null){}

    /**
     * @param $key[required]
     * @param $min[required]
     * @param $max[required]
     * @param $offset[optional]
     * @param $limit[optional]
     * @return mixed
     */
    public function zRangeByLex($key, $min, $max, $offset = null, $limit = null){}

    /**
     * @param $key[required]
     * @param $min[required]
     * @param $max[required]
     * @param $offset[optional]
     * @param $limit[optional]
     * @return mixed
     */
    public function zRevRangeByLex($key, $min, $max, $offset = null, $limit = null){}

    /**
     * @param $key[required]
     * @param $keys[required]
     * @param $weights[optional]
     * @param $aggregate[optional]
     * @return mixed
     */
    public function zInter($key, $keys, $weights = null, $aggregate = null){}

    /**
     * @param $key[required]
     * @param $keys[required]
     * @param $weights[optional]
     * @param $aggregate[optional]
     * @return mixed
     */
    public function zinterstore($key, $keys, $weights = null, $aggregate = null){}

    /**
     * @param $key[required]
     * @param $keys[required]
     * @param $weights[optional]
     * @param $aggregate[optional]
     * @return mixed
     */
    public function zUnion($key, $keys, $weights = null, $aggregate = null){}

    /**
     * @param $key[required]
     * @param $keys[required]
     * @param $weights[optional]
     * @param $aggregate[optional]
     * @return mixed
     */
    public function zunionstore($key, $keys, $weights = null, $aggregate = null){}

    /**
     * @param $key[required]
     * @param $value[required]
     * @return mixed
     */
    public function incrBy($key, $value){}

    /**
     * @param $key[required]
     * @param $member[required]
     * @param $value[required]
     * @return mixed
     */
    public function hIncrBy($key, $member, $value){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function incr($key){}

    /**
     * @param $key[required]
     * @param $value[required]
     * @return mixed
     */
    public function decrBy($key, $value){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function decr($key){}

    /**
     * @param $key[required]
     * @param $offset[required]
     * @return mixed
     */
    public function getBit($key, $offset){}

    /**
     * @param $key[required]
     * @param $position[required]
     * @param $pivot[required]
     * @param $value[required]
     * @return mixed
     */
    public function lInsert($key, $position, $pivot, $value){}

    /**
     * @param $key[required]
     * @param $index[required]
     * @return mixed
     */
    public function lGet($key, $index){}

    /**
     * @param $key[required]
     * @param $integer[required]
     * @return mixed
     */
    public function lIndex($key, $integer){}

    /**
     * @param $key[required]
     * @param $timeout[required]
     * @return mixed
     */
    public function setTimeout($key, $timeout){}

    /**
     * @param $key[required]
     * @param $integer[required]
     * @return mixed
     */
    public function expire($key, $integer){}

    /**
     * @param $key[required]
     * @param $timestamp[required]
     * @return mixed
     */
    public function pexpire($key, $timestamp){}

    /**
     * @param $key[required]
     * @param $timestamp[required]
     * @return mixed
     */
    public function expireAt($key, $timestamp){}

    /**
     * @param $key[required]
     * @param $timestamp[required]
     * @return mixed
     */
    public function pexpireAt($key, $timestamp){}

    /**
     * @param $key[required]
     * @param $dbindex[required]
     * @return mixed
     */
    public function move($key, $dbindex){}

    /**
     * @param $dbindex[required]
     * @return mixed
     */
    public function select($dbindex){}

    /**
     * @param $key[required]
     * @param $start[required]
     * @param $end[required]
     * @return mixed
     */
    public function getRange($key, $start, $end){}

    /**
     * @param $key[required]
     * @param $start[required]
     * @param $stop[required]
     * @return mixed
     */
    public function listTrim($key, $start, $stop){}

    /**
     * @param $key[required]
     * @param $start[required]
     * @param $stop[required]
     * @return mixed
     */
    public function ltrim($key, $start, $stop){}

    /**
     * @param $key[required]
     * @param $start[required]
     * @param $end[required]
     * @return mixed
     */
    public function lGetRange($key, $start, $end){}

    /**
     * @param $key[required]
     * @param $start[required]
     * @param $end[required]
     * @return mixed
     */
    public function lRange($key, $start, $end){}

    /**
     * @param $key[required]
     * @param $value[required]
     * @param $count[required]
     * @return mixed
     */
    public function lRem($key, $value, $count){}

    /**
     * @param $key[required]
     * @param $value[required]
     * @param $count[required]
     * @return mixed
     */
    public function lRemove($key, $value, $count){}

    /**
     * @param $key[required]
     * @param $start[required]
     * @param $end[required]
     * @return mixed
     */
    public function zDeleteRangeByRank($key, $start, $end){}

    /**
     * @param $key[required]
     * @param $min[required]
     * @param $max[required]
     * @return mixed
     */
    public function zRemRangeByRank($key, $min, $max){}

    /**
     * @param $key[required]
     * @param $value[required]
     * @return mixed
     */
    public function incrByFloat($key, $value){}

    /**
     * @param $key[required]
     * @param $member[required]
     * @param $value[required]
     * @return mixed
     */
    public function hIncrByFloat($key, $member, $value){}

    /**
     * @param $key[required]
     * @return mixed
     */
    public function bitCount($key){}

    /**
     * @param $operation[required]
     * @param $ret_key[required]
     * @param $key[required]
     * @param $other_keys[optional]
     * @return mixed
     */
    public function bitOp($operation, $ret_key, $key, $other_keys = null){}

    /**
     * @param $key[required]
     * @param $value[required]
     * @return mixed
     */
    public function sAdd($key, $value){}

    /**
     * @param $src[required]
     * @param $dst[required]
     * @param $value[required]
     * @return mixed
     */
    public function sMove($src, $dst, $value){}

    /**
     * @param $key[required]
     * @param $other_keys[optional]
     * @return mixed
     */
    public function sDiff($key, $other_keys = null){}

    /**
     * @param $dst[required]
     * @param $key[required]
     * @param $other_keys[optional]
     * @return mixed
     */
    public function sDiffStore($dst, $key, $other_keys = null){}

    /**
     * @param $key[required]
     * @param $other_keys[optional]
     * @return mixed
     */
    public function sUnion($key, $other_keys = null){}

    /**
     * @param $dst[required]
     * @param $key[required]
     * @param $other_keys[optional]
     * @return mixed
     */
    public function sUnionStore($dst, $key, $other_keys = null){}

    /**
     * @param $key[required]
     * @param $other_keys[optional]
     * @return mixed
     */
    public function sInter($key, $other_keys = null){}

    /**
     * @param $dst[required]
     * @param $key[required]
     * @param $other_keys[optional]
     * @return mixed
     */
    public function sInterStore($dst, $key, $other_keys = null){}

    /**
     * @param $key[required]
     * @param $value[required]
     * @return mixed
     */
    public function sRemove($key, $value){}

    /**
     * @param $key[required]
     * @param $value[required]
     * @return mixed
     */
    public function srem($key, $value){}

    /**
     * @param $key[required]
     * @param $member[required]
     * @param $other_members[optional]
     * @return mixed
     */
    public function zDelete($key, $member, $other_members = null){}

    /**
     * @param $key[required]
     * @param $member[required]
     * @param $other_members[optional]
     * @return mixed
     */
    public function zRemove($key, $member, $other_members = null){}

    /**
     * @param $key[required]
     * @param $member[required]
     * @param $other_members[optional]
     * @return mixed
     */
    public function zRem($key, $member, $other_members = null){}

    /**
     * @param $patterns[required]
     * @return mixed
     */
    public function pSubscribe($patterns){}

    /**
     * @param $channels[required]
     * @return mixed
     */
    public function subscribe($channels){}

    /**
     * @return mixed
     */
    public function multi(){}

    /**
     * @return mixed
     */
    public function exec(){}

    /**
     * @param $script[required]
     * @param $args[optional]
     * @param $num_keys[optional]
     * @return mixed
     */
    public function eval($script, $args = null, $num_keys = null){}

    /**
     * @param $script_sha[required]
     * @param $args[optional]
     * @param $num_keys[optional]
     * @return mixed
     */
    public function evalSha($script_sha, $args = null, $num_keys = null){}

    /**
     * @param $cmd[required]
     * @param $args[optional]
     * @return mixed
     */
    public function script($cmd, $args = null){}


}
