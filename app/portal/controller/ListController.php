<?php
namespace app\portal\controller;

use cmf\controller\HomeBaseController;
use app\portal\model\PortalCategoryModel;

class ListController extends HomeBaseController
{
    public function index()
    {
        $id                  = $this->request->param('id', 0, 'intval');
        $portalCategoryModel = new PortalCategoryModel();

        $category = $portalCategoryModel->where('id', $id)->where('status', 1)->find();

        $this->assign('category', $category);

        $listTpl = empty($category['list_tpl']) ? 'list' : $category['list_tpl'];

        return $this->fetch('/' . $listTpl);
    }
    /**
     * 获取共享ur，后台用
     * @todo 这个地方有可能会修改，临时兼容用下，老猫，你要改的时候再改吧。。
     */
    public function nav_index(){
        $navcatname="文章分类";
        $datas=cmf_get_terms("field:id,name");
        $navrule=array(
            "action"=>"List/index",
            "param"=>array(
                "id"=>"id"
            ),
            "label"=>"name");
        exit(json_encode(cmf_get_nav4admin($navcatname,$datas,$navrule)));

    }
}
