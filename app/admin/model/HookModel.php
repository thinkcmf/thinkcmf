<?php

namespace app\admin\model;

use think\Model;

class HookModel extends Model
{

    public function plugins()
    {
        return $this->belongsToMany('PluginModel', 'hook_plugin', 'plugin', 'hook');
    }

}