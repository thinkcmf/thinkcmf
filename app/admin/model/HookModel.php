<?php
namespace app\admin\model;

use think\Model;

class HookModel extends Model
{

    public function plugins(){
        $prefix = $this->getConfig('prefix');
        return $this->belongsToMany('PluginModel', $prefix . 'hook_plugin', 'plugin', 'hook');
    }

}