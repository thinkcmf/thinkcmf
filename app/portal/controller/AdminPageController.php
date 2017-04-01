<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\portal\controller;

use cmf\controller\AdminBaseController;
use app\portal\model\PortalPostModel;
use app\portal\service\PostService;

use think\Db;

class AdminPageController extends AdminBaseController
{

    // 页面列表
    public function index()
    {


        $param = $this->request->param();

        $postService = new PostService();
        $data        = $postService->adminPageList($param);

        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');
        $this->assign('pages', $data->items());
        $this->assign('page', $data->render());

        return $this->fetch();
    }

    // 添加页面
    public function add()
    {
        return $this->fetch();
    }

    // 添加页面提交保存
    public function addPost()
    {


        $data = $this->request->param();

        $portalPostModel         = new PortalPostModel();
        $data['post']['more']    = json_encode($data['more']);

        $portalPostModel->adminAddPage($data['post']);

        $this->success(lang('ADD_SUCCESS'));


    }

    // 编辑页面
    public function edit()
    {
        $id = $this->request->param('id', 0, 'intval');

        $portalPostModel = new PortalPostModel();
        $post            = $portalPostModel->where('id', $id)->find();
        $more            = json_decode($post['more'],true);

        $this->assign('more', $more);
        $this->assign('post', $post);

        return $this->fetch();
    }

    // 编辑页面提交保存
    public function editPost()
    {

        $data = $this->request->param();

        $portalPostModel = new PortalPostModel();


        $data['post']['more']    = json_encode($data['more']);

        $portalPostModel->adminEditPage($data['post']);

        $this->success(lang('SAVE_SUCCESS'));

    }


    /**
     * @todo db操作不应该放模型里面更好么？
     * 页面管理删除方法
     * @copyright [copyright]
     * @license   [license]
     * @version   [version]
     * @author    iyting@foxmail.com
     * @time      2017-03-28T11:02:47+0800
     * @return    [type]
     */
    public function delete()
    {

        $portalPostModel = new PortalPostModel();
        if(input('?param.id')){
            $id  = input('param.id/d'); //获取删除id

            $res = $portalPostModel->where(['id' => $id])->find();
            if($res){
                $res =  json_decode(json_encode($res),true); //转换为数组
                $recycleData   = [
                    'object_id'   => $res['id'],
                    'create_time' => time(),
                    'table_name'  => 'portal_post',
                    'name'        => $res['post_title'],
                    'data'        => json_encode($res)
                ];
                Db::startTrans(); //开启事务
                $transStatus = false;
                try{
                    Db::name('portal_post')->where(['id' => $id])->update([
                                                    'post_status' => 3,
                                                    'delete_time' => time()
                                                  ]);
                    Db::name('recycle_bin')->insert($recycleData);

                    $transStatus = true;
                    // 提交事务
                    Db::commit();
                } catch (\Exception $e) {
                    $transStatus = false;
                    // 回滚事务
                    Db::rollback();


                }

                if($transStatus){
                    $this->success(lang('DELETE_SUCCESS'));
                }else{
                    $this->error(lang('DELETE_FAILED'));
                }

            }else{
                $this->error(lang('DELETE_FAILED'));
            }
        }elseif(input('?param.ids')){
            $ids = input('param.ids/a');
            $res = $portalPostModel->where(['id' => ['in',$ids]])
                                   ->select();

            if($res){
                $res =  json_decode(json_encode($res),true);
                foreach ($res as $key => $value) {
                    $recycleData[$key]['object_id'] = $value['id'];
                    $recycleData[$key]['create_time'] = time();
                    $recycleData[$key]['table_name'] = 'portal_post';
                    $recycleData[$key]['name'] = $value['post_title'];
                    $recycleData[$key]['data'] = json_encode($value);
                }

                Db::startTrans(); //开启事务
                $transStatus = false;
                try{
                    Db::name('portal_post')->where(['id' => ['in',$ids]])
                                              ->update([
                                                    'post_status' => 3,
                                                    'delete_time' => time()
                                                  ]);


                    Db::name('recycle_bin')->insertAll($recycleData);

                    $transStatus = true;
                    // 提交事务
                    Db::commit();

                } catch (\Exception $e) {
                    $transStatus = false;

                    // 回滚事务
                    Db::rollback();


                }
                if($transStatus){
                    $this->success(lang('DELETE_SUCCESS'));
                }else{
                    $this->error(lang('DELETE_FAILED'));
                }

            }else{
                $this->error(lang('DELETE_FAILED'));
            }

        }else{
            $this->error(lang('DELETE_FAILED'));
        }

    }
    /**
     * 后台页面回收站
     * @copyright [copyright]
     * @license   [license]
     * @version   [version]
     * @author
     * @time      2017-03-31T13:45:31+0800
     * @return    [type]
     */
    public function recyclebin(){

        //$this->_lists(array('post_status'=>array('eq',3)));


        return $this->fetch();
    }

}
