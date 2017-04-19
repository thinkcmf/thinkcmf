<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\portal\service;

use app\portal\model\PortalPostModel;
use think\Db;

class ApiService
{
    /**
     * 功能:查询文章列表,支持分页;<br>
     * 注:此方法查询时关联三个表portal_category_post,portal_post,user;在指定查询字段(field),排序(order),指定查询条件(where)最好指定一下表名
     * @param array $param 查询参数<pre>
     * array(
     *  'field'=>'*',
     *  'where'=>'',
     *  'limit'=>'',
     *  'order'=>'',
     *  'page'=>'',
     *  'relation'=>''
     * )
     * 字段说明:
     * ids:文章id,可以指定一个或多个文章id,以英文逗号分隔,如1或1,2,3
     * category_ids:文章所在分类,可指定一个或多个分类id,以英文逗号分隔,如1或1,2,3 默认值为全部
     * field:调用指定的字段
     *   如只调用posts表里的id和post_title字段可以是field:posts.id,posts.post_title; 默认全部,
     *   此方法查询时关联三个表term_relationships,posts,users;
     *   所以最好指定一下表名,以防字段冲突
     * limit:数据条数,默认值为10,可以指定从第几条开始,如3,8(表示共调用8条,从第3条开始)
     * order:排序方式,如按posts表里的post_date字段倒序排列：posts.post_date desc
     * where:查询条件,字符串形式,和sql语句一样,请在事先做好安全过滤,最好使用第二个参数$where的数组形式进行过滤,此方法查询时关联多个表,所以最好指定一下表名,以防字段冲突,查询条件(只支持数组),格式和thinkPHP的where方法一样,此方法查询时关联多个表,所以最好指定一下表名,以防字段冲突;
     * </pre>
     * @return array 包括分页的文章列表<pre>
     * 格式:
     * array(
     *     "articles"=>array(),//文章列表,array
     *     "page"=>"",//生成的分页html,不分页则没有此项
     *     "count"=>100, //符合条件的文章总数,不分页则没有此项
     *     "total_pages"=>5 // 总页数
     * )</pre>
     */
    public static function articles($param)
    {
        $portalPostModel = new PortalPostModel();

        $where = [
            'post.published_time' => [['> time', 0], ['<', time()]],
            'post.post_status'    => ['eq', 1],
            'post.post_type'      => 1
        ];

        $paramWhere = empty($param['where']) ? '' : $param['where'];

        $limit        = empty($param['limit']) ? 10 : $param['limit'];
        $order        = empty($param['order']) ? '' : $param['order'];
        $page         = isset($param['page']) ? $param['page'] : false;
        $relation     = empty($param['relation']) ? '' : $param['relation'];
        $category_ids = empty($param['category_ids']) ? '' : $param['category_ids'];

        $join = [
            ['__USER__ user', 'post.user_id = user.id'],
            ['__PORTAL_CATEGORY_POST__ category_post', 'post.id = category_post.post_id']
        ];

        if (!empty($category_ids)) {

            if (!is_array($category_ids)) {
                $category_ids = explode(',', $category_ids);
            }

            if (count($category_ids) == 1) {
                $where['category_post.category_id'] = ['eq', $category_ids[0]];
            } else {
                $where['category_post.category_id'] = ['in', $category_ids];
            }
        }

        $articles = $portalPostModel->alias('post')->field('post.*,user.user_login,user.user_nickname,user.user_email,category_post.category_id')
            ->join($join)
            ->where($where)
            ->where($paramWhere)
            ->order($order);

        $return = [];

        if (empty($page)) {
            $articles           = $articles->limit($limit)->select();
            $return['articles'] = $articles;
        } else {

            if (is_array($page)) {
                if (empty($page['list_rows'])) {
                    $page['list_rows'] = 10;
                }

                $articles = $articles->paginate($page);
            } else {
                $articles = $articles->paginate(intval($page));
            }
            $return['articles'] = $articles->items();
            $return['page']     = $articles->render();
            $return['total']    = $articles->total();
        }

        return $return;

    }

    /**
     * @todo
     * 获取指定id的文章
     */
    public static function post($postId, $tag)
    {

    }

    /**
     * @todo
     * 获取指定条件的页面列表
     */
    public static function pages($tag, $where = [])
    {
    }

    /**
     * @todo
     * 获取指定id的页面
     * @param int $id 页面的id
     * @return array 返回符合条件的页面
     */
    public static function page($id)
    {
    }

    /**
     * @todo
     * 返回指定分类
     * @param int $categoryId 分类id
     * @return array 返回符合条件的分类
     */
    public static function category($categoryId)
    {
    }

    /**
     * @todo
     * 返回指定分类下的子分类
     * @param int $categoryId 分类id
     * @return array 返回指定分类下的子分类X
     */
    public static function subCategories($categoryId)
    {
    }

    /**
     * @todo
     * 返回指定分类下的所有子分类
     * @param int $categoryId 分类id
     * @return array 返回指定分类下的所有子分类
     */
    public static function allSubCategories($categoryId)
    {
    }

    /**
     * @todo
     * 返回符合条件的所有分类
     *
     */
    public static function terms()
    {
    }

    /**
     * 获取面包屑数据
     * @param int $categoryId 当前文章所在分类,或者当前分类的id
     * @param boolean $withCurrent 是否获取当前分类
     * @return array 面包屑数据
     */
    public static function breadcrumb($categoryId, $withCurrent = false)
    {
        $data = [];
        $path = Db::name('portal_category')->where(['id' => $categoryId])->value('path');
        if (!empty($path)) {
            $parents = explode('-', $path);
            if (!$withCurrent) {
                array_pop($parents);
            }

            if (!empty($parents)) {
                $data = Db::name('portal_category')->where(['id' => ['in', $parents]])->order('path ASC')->select();
            }
        }

        return $data;
    }

}