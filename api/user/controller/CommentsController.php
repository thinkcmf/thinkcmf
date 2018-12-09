<?php
// +----------------------------------------------------------------------
// | 文件说明：评论
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: wuwu <15093565100@163.com>
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Date: 2017-7-26
// +----------------------------------------------------------------------
namespace api\user\controller;

use api\user\model\CommentModel as Comment;
use api\user\model\UserModel as User;
use cmf\controller\RestBaseController;

class CommentsController extends RestBaseController
{

    /**
     * [getUserComments 获取用户评论]
     * @Author:   wuwu<15093565100@163.com>
     * @DateTime: 2017-05-25T20:48:53+0800
     * @since:    1.0
     * @return    [array_json] [获取Comment]
     */
    public function getUserComments()
    {
        $input = $this->request->param();

        $comment                 = new Comment();
        $map['where']['user_id'] = $this->getUserId();
        $map['order']            = '-create_time';
        $map['relation']         = 'user,to_user';
        if (!empty($input['page'])) {
            $map['page'] = $input['page'];
        }
        //处理不同的情况
        $data = $comment->getDatas($map);

        if (empty($this->apiVersion) || $this->apiVersion == '1.0.0') {
            $response = [$data];
        } else {
            $response = ['list' => $data];
        }

        $this->success('请求成功', $response);

    }

    /**
     * [getComments 获取评论]
     * @Author:   wuwu<15093565100@163.com>
     * @DateTime: 2017-05-25T20:48:53+0800
     * @since:    1.0
     * @return    [array_json] [获取Comment]
     */
    public function getComments()
    {
        $input           = $this->request->param();
        $id              = $this->request->has('object_id') ? $input['object_id'] : $this->error('id参数不存在');
        $table           = $this->request->has('table_name') ? $input['table_name'] : $this->error('table参数不存在');
        $comment         = new Comment();
        $map['where']    = [
            'object_id'  => $id,
            'table_name' => $table
        ];
        $map['relation'] = 'user,to_user';

        if (!empty($input['page'])) {
            $map['page'] = $input['page'];
        }

        $data = $comment->getDatas($map);

        if (empty($this->apiVersion) || $this->apiVersion == '1.0.0') {
            $response = [$data];
        } else {
            $response = ['list' => $data];
        }

        //数据是否存在
        if ($data->isEmpty()) {
            $this->error('评论数据为空');
        } else {
            $this->success('评论获取成功!', $response);
        }
    }

    /**
     * [delComments 删除评论]
     * @Author:   wuwu<15093565100@163.com>
     * @DateTime: 2017-08-11T22:08:56+0800
     * @since:    1.0
     * @return
     */
    public function delComments()
    {
        $input  = $this->request->param();
        $id     = $this->request->has('id') ? intval($input['id']) : $this->error('id参数不存在');
        $userId = $this->getUserId();
        Comment::destroy(['id' => $id, 'user_id' => $userId]);

        $this->success('删除成功');
    }

    /**
     * [setComments 添加评论]
     * @Author:   wuwu<15093565100@163.com>
     * @DateTime: 2017-08-16T01:07:44+0800
     * @since:    1.0
     */
    public function setComments()
    {
        $data = $this->_setComments();
        if ($res = Comment::setComment($data)) {
            $this->success('评论成功', $res);
        } else {
            $this->error('评论失败');
        }
    }

    /**
     * [_setComments 评论数据组织]
     * @Author:   wuwu<15093565100@163.com>
     * @DateTime: 2017-08-16T01:00:02+0800
     * @since:    1.0
     */
    protected function _setComments()
    {
        $input              = $this->request->param();
        $data['object_id']  = $this->request->has('object_id') ? $input['object_id'] : $this->error('object_id参数不存在');
        $data['table_name'] = $this->request->has('table_name') ? $input['table_name'] : $this->error('table_name参数不存在');
        $data['url']        = $this->request->has('url') ? $input['url'] : $this->error('url参数不存在');
        $data['content']    = $this->request->has('content') ? $input['content'] : $this->error('内容不为空');
        $data['parent_id']  = $this->request->has('parent_id') ? $input['parent_id'] : 0;
        $result             = $this->validate($data,
            [
                'object_id' => 'require|number',
                'content'   => 'require',
            ]);
        if (true !== $result) {
            // 验证失败 输出错误信息
            $this->error($result);
        }
        $data['delete_time'] = 0;
        $data['create_time'] = time();
        if ($data['parent_id']) {
            $res = Comment::field(['parent_id', 'path', 'user_id'])->find($data['parent_id']);
            if ($res) {
                $data['path']       = $res['path'] . $data['parent_id'] . ',';
                $data['to_user_id'] = $res['user_id'];
            } else {
                $this->error('回复的评论不存在');
            }
        } else {
            $data['path'] = '0,';
        }
        $data['user_id'] = $this->getUserId();
        $userData        = User::field(true)->find($data['user_id']);
        if (!$userData) {
            $this->error('评论用户不存在');
        }

        $data['full_name'] = $userData['user_nickname'];
        $data['email']     = $userData['user_email'];
        return $data;
    }
}
