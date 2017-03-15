<?php
namespace app\portal\controller;

use cmf\controller\HomeBaseController;

class PageController extends HomeBaseController
{
    public function index()
    {
        return $this->fetch('/page');
    }

    public function nav_index(){
        $navcatname="页面";
        $datas=cmf_sql_pages("field:id,post_title;");
        $navrule=array(
            "action"=>"Page/index",
            "param"=>array(
                "id"=>"id"
            ),
            "label"=>"post_title");
        exit( json_encode(cmf_get_nav4admin($navcatname,$datas,$navrule)) );
    }
}
