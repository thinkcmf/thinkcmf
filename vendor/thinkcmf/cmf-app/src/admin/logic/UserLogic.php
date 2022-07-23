<?php
namespace app\admin\logic;

class UserLogic
{
    public static function isCreator()
    {
        return (cmf_get_current_admin_id() == 1);
    }
}