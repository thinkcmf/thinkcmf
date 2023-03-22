<?php

namespace cmf\auth\service;

use Casbin\Bridge\Logger\LoggerBridge;
use Casbin\Enforcer;
use Casbin\Model\Model;
use Casbin\Log\Log;
use think\Service;
use tauthz\command\Publish;

/**
 * Tauthz service.
 *
 * @author techlee@qq.com
 */
class AuthService
{
    /**
     * Register service.
     *
     * @return void
     */
    public static function check($sub, $obj, $act)
    {
        $modelStr = <<<hello
[request_definition]
r = sub, obj, act

[policy_definition]
p = sub, obj, act

[role_definition]
g = _, _

[policy_effect]
e = some(where (p.eft == allow))

[matchers]
m = g(r.sub, p.sub) && r.obj == p.obj && r.act == p.act
hello;

        $model = new Model();
        $model->loadModelFromText($modelStr);

        $adapter  = app(\cmf\auth\adapter\DatabaseAdapter::class);
        $enforcer = new Enforcer();
        $enforcer->loadPolicy();
        $enforcer->initWithModelAndAdapter($model, $adapter);
        $enforcer->loadFilteredPolicy(function ($query) {
////            $query->whereRaw('1=1');
        });

        return $enforcer->enforce($sub, $obj, $act);
    }
}
