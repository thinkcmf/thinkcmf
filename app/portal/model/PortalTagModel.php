<?php
namespace app\portal\model;

use think\Model;

class PortalTagModel extends Model
{
    public static   $STATUS = array(
        0=>"未启用",
        1=>"已启用",
    );
}