<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\portal\controller;

use cmf\controller\HomeBaseController;

class SearchController extends HomeBaseController
{
    public function index()
    {
        $keyword = $this->request->param('keyword');
        $time = session('sear_time');
        if ($time > (THINK_START_TIME-5)) {
            
            $data['status'] = 0;
            $data['info'] = '操作频繁';
            return json($data);
        }else{
            session('sear_time',THINK_START_TIME);
        }

        if(empty($keyword)){
            $data['status'] = 0;
            $data['info'] = '关键词为空';
            return json($data);
        }
        $list = db('portal_post')
         -> field(true)
         -> where('post_keywords','like','%'.$keyword.'%')
         -> order('id DESC')
         -> select();
        if(empty($list)){
            $data['status'] = 0;
            $data['info'] = $keyword.' 搜索不到';
            return json($data);
        }
        $data['status'] = 1;
        $data['info'] = $list;
        return json($data);
    }
}
