<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: pl125 <xskjs888@163.com>
// +----------------------------------------------------------------------
namespace api\portal\controller;

use api\portal\model\PortalPostModel;
use cmf\controller\RestBaseController;
use api\portal\model\PortalTagModel;

class TagsController extends RestBaseController
{
    protected $tagModel;

    public function __construct(PortalTagModel $tagModel)
    {
        parent::__construct();
        $this->tagModel = $tagModel;
    }

    /**
     * 获取标签列表
     */
    public function index()
    {
        $params = $this->request->get();
        $data   = $this->tagModel->getDatas($params);

        if (empty($this->apiVersion) || $this->apiVersion == '1.0.0') {
            $response = $data;
        } else {
            $response = ['list' => $data,];
        }
        $this->success('请求成功!', $response);
    }

    /**
     * 获取热门标签列表
     */
    public function hotTags()
    {
        $params                         = $this->request->get();
        $params['where']['recommended'] = 1;
        $data                           = $this->tagModel->getDatas($params);

        if (empty($this->apiVersion) || $this->apiVersion == '1.0.0') {
            $response = $data;
        } else {
            $response = ['list' => $data,];
        }
        $this->success('请求成功!', $response);
    }

    /**
     * 获取标签文章列表
     * @param int $id
     */
    public function articles($id)
    {
        if (intval($id) === 0) {
            $this->error('无效的标签id！');
        } else {
            $params    = $this->request->param();
            $postModel = new PortalPostModel();

            unset($params['id']);

            $articles = $postModel->paramsFilter($params)->alias('post')
                ->join('__PORTAL_TAG_POST__ tag_post', 'post.id = tag_post.post_id')
                ->where(['tag_post.tag_id' => $id])->select();

            if (!empty($params['relation'])) {
                $allowedRelations = $postModel->allowedRelations($params['relation']);
                if (!empty($allowedRelations)) {
                    if (count($articles) > 0) {
                        $articles->load($allowedRelations);
                        $articles->append($allowedRelations);
                    }
                }
            }


            $this->success('请求成功!', ['articles' => $articles]);
        }
    }
}
