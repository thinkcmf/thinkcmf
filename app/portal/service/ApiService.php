<?php
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
     * 查询文章列表,不做分页
     * 注:此方法查询时关联三个表term_relationships,posts,users;在指定查询字段(field),排序(order),指定查询条件(where)最好指定一下表名
     * @param string $tag <pre>查询标签,以字符串方式传入
     * 例："cid:1,2;field:posts.post_title,posts.post_content;limit:0,8;order:post_date desc,listorder desc;where:id>0;"
     * ids:文章id,可以指定一个或多个文章id,以英文逗号分隔,如1或1,2,3
     * cid:文章所在分类,可指定一个或多个分类id,以英文逗号分隔,如1或1,2,3 默认值为全部
     * field:调用指定的字段
     *   如只调用posts表里的id和post_title字段可以是field:posts.id,posts.post_title; 默认全部,
     *   此方法查询时关联三个表term_relationships,posts,users;
     *   所以最好指定一下表名,以防字段冲突
     * limit:数据条数,默认值为10,可以指定从第几条开始,如3,8(表示共调用8条,从第3条开始)
     * order:排序方式,如按posts表里的post_date字段倒序排列：posts.post_date desc
     * where:查询条件,字符串形式,和sql语句一样,请在事先做好安全过滤,最好使用第二个参数$where的数组形式进行过滤,此方法查询时关联多个表,所以最好指定一下表名,以防字段冲突</pre>
     * @param array $where 查询条件(只支持数组),格式和thinkPHP的where方法一样,此方法查询时关联多个表,所以最好指定一下表名,以防字段冲突;
     * @return array 文章列表
     */
    public static function postsNotPaged($tag, $where = [])
    {
        $content = self::posts($tag, $where);
        return $content['posts'];
    }

    /**
     * 功能：根据分类文章分类ID 获取该分类下所有文章(包含子分类中文章)
     * 注:此方法查询时关联三个表term_relationships,posts,users;在指定查询字段(field),排序(order),指定查询条件(where)最好指定一下表名
     * @author labulaka 2014-11-09 14:30:49
     * @param int $categoryId 文章分类ID.
     * @param string $tag <pre>查询标签,以字符串方式传入
     * 例："cid:1,2;field:posts.post_title,posts.post_content;limit:0,8;order:post_date desc,listorder desc;where:id>0;"
     * ids:文章id,可以指定一个或多个文章id,以英文逗号分隔,如1或1,2,3
     * cid:文章所在分类,可指定一个或多个分类id,以英文逗号分隔,如1或1,2,3 默认值为全部
     * field:调用指定的字段
     *   如只调用posts表里的id和post_title字段可以是field:posts.id,posts.post_title; 默认全部,
     *   此方法查询时关联三个表term_relationships,posts,users;
     *   所以最好指定一下表名,以防字段冲突
     * limit:数据条数,默认值为10,可以指定从第几条开始,如3,8(表示共调用8条,从第3条开始)
     * order:排序方式,如按posts表里的post_date字段倒序排列：posts.post_date desc
     * where:查询条件,字符串形式,和sql语句一样,请在事先做好安全过滤,最好使用第二个参数$where的数组形式进行过滤,此方法查询时关联多个表,所以最好指定一下表名,以防字段冲突</pre>
     * @param array $where 查询条件(只支持数组),格式和thinkPHP的where方法一样,此方法查询时关联多个表,所以最好指定一下表名,以防字段冲突;
     * @return 文章列表
     */
    public static function postsByTermId($categoryId, $tag, $where = [])
    {
        $categoryId = intval($categoryId);

        if (!is_array($where)) {
            $where = [];
        }

        $categoryIds = M("Terms")->where("status=1 and ( term_id=$categoryId OR path like '%-$categoryId-%' )")->order('term_id asc')->getField('term_id', true);

        if (!empty($categoryIds)) {
            $where['term_relationships.term_id'] = ['in', $categoryIds];
        }

        $content = self::posts($tag, $where);

        return $content['posts'];
    }

    /**
     * 功能:查询文章列表,支持分页;<br>
     * 注:此方法查询时关联三个表term_relationships,posts,users;在指定查询字段(field),排序(order),指定查询条件(where)最好指定一下表名
     * @param string $tag <pre>查询标签,以字符串方式传入
     * 例："cid:1,2;field:posts.post_title,posts.post_content;limit:0,8;order:post_date desc,listorder desc;where:id>0;"
     * ids:文章id,可以指定一个或多个文章id,以英文逗号分隔,如1或1,2,3
     * cid:文章所在分类,可指定一个或多个分类id,以英文逗号分隔,如1或1,2,3 默认值为全部
     * field:调用指定的字段
     *   如只调用posts表里的id和post_title字段可以是field:posts.id,posts.post_title; 默认全部,
     *   此方法查询时关联三个表term_relationships,posts,users;
     *   所以最好指定一下表名,以防字段冲突
     * limit:数据条数,默认值为10,可以指定从第几条开始,如3,8(表示共调用8条,从第3条开始)
     * order:排序方式,如按posts表里的post_date字段倒序排列：posts.post_date desc
     * where:查询条件,字符串形式,和sql语句一样,请在事先做好安全过滤,最好使用第二个参数$where的数组形式进行过滤,此方法查询时关联多个表,所以最好指定一下表名,以防字段冲突</pre>
     * @param int $pageSize 每页条数,为0,false表示不分页
     * @param string $pageTpl 以字符串方式传入,例："{first}{prev}{liststart}{list}{listend}{next}{last}"
     * @return array 包括分页的文章列表<pre>
     * 格式:
     * array(
     *     "posts"=>array(),//文章列表,array
     *       "page"=>""//生成的分页html,不分页则没有此项
     *     "count"=>100 //符合条件的文章总数,不分页则没有此项
     * )</pre>
     */
    public static function postsPaged($tag, $pageSize = 20, $pageTpl = '')
    {
        return self::posts($tag, [], $pageSize, $pageTpl);
    }

    /**
     * 根据分类文章分类ID 获取该分类下所有文章（包含子分类中文章）,已经分页
     * 注:此方法查询时关联三个表term_relationships,posts,users;在指定查询字段(field),排序(order),指定查询条件(where)最好指定一下表名
     * @author labulaka 2014-11-09 14:30:49
     * @param int $categoryId 文章分类ID.
     * @param string $tag <pre>查询标签,以字符串方式传入
     * 例："cid:1,2;field:posts.post_title,posts.post_content;limit:0,8;order:post_date desc,listorder desc;where:id>0;"
     * ids:文章id,可以指定一个或多个文章id,以英文逗号分隔,如1或1,2,3
     * cid:文章所在分类,可指定一个或多个分类id,以英文逗号分隔,如1或1,2,3 默认值为全部
     * field:调用指定的字段
     *   如只调用posts表里的id和post_title字段可以是field:posts.id,posts.post_title; 默认全部,
     *   此方法查询时关联三个表term_relationships,posts,users;
     *   所以最好指定一下表名,以防字段冲突
     * limit:数据条数,默认值为10,可以指定从第几条开始,如3,8(表示共调用8条,从第3条开始)
     * order:排序方式,如按posts表里的post_date字段倒序排列：posts.post_date desc
     * where:查询条件,字符串形式,和sql语句一样,请在事先做好安全过滤,最好使用第二个参数$where的数组形式进行过滤,此方法查询时关联多个表,所以最好指定一下表名,以防字段冲突</pre>
     * @param int $pageSize 每页条数.
     * @param string $pageTpl 以字符串方式传入,例："{first}{prev}{liststart}{list}{listend}{next}{last}"
     * @return array 文章列表
     */
    public static function postsPagedByTermId($categoryId, $tag, $pageSize = 20, $pageTpl = '')
    {
        $categoryId  = intval($categoryId);
        $where       = [];
        $categoryIds = M("Terms")->field("term_id")->where("status=1 and ( term_id=$categoryId OR path like '%-$categoryId-%' )")->order('term_id asc')->getField('term_id', true);

        if (!empty($categoryIds)) {
            $where['term_relationships.term_id'] = ['in', $categoryIds];
        }

        $content = self::posts($tag, $where, $pageSize, $pageTpl);

        return $content;
    }

    /**
     * 功能：根据关键字 搜索文章（包含子分类中文章）,已经分页,调用方式同sp_sql_posts_paged<br>
     * 注:此方法查询时关联三个表term_relationships,posts,users;在指定查询字段(field),排序(order),指定查询条件(where)最好指定一下表名
     * @author WelkinVan 2014-12-04
     * @param string $keyword 关键字.
     * @param string $tag <pre>查询标签,以字符串方式传入
     * 例："cid:1,2;field:posts.post_title,posts.post_content;limit:0,8;order:post_date desc,listorder desc;where:id>0;"
     * ids:文章id,可以指定一个或多个文章id,以英文逗号分隔,如1或1,2,3
     * cid:文章所在分类,可指定一个或多个分类id,以英文逗号分隔,如1或1,2,3 默认值为全部
     * field:调用指定的字段
     *   如只调用posts表里的id和post_title字段可以是field:posts.id,posts.post_title; 默认全部,
     *   此方法查询时关联三个表term_relationships,posts,users;
     *   所以最好指定一下表名,以防字段冲突
     * limit:数据条数,默认值为10,可以指定从第几条开始,如3,8(表示共调用8条,从第3条开始)
     * order:排序方式,如按posts表里的post_date字段倒序排列：posts.post_date desc
     * where:查询条件,字符串形式,和sql语句一样,请在事先做好安全过滤,最好使用第二个参数$where的数组形式进行过滤,此方法查询时关联多个表,所以最好指定一下表名,以防字段冲突</pre>
     * @param int $pageSize 每页条数.
     * @param string $pageTpl 以字符串方式传入,例："{first}{prev}{liststart}{list}{listend}{next}{last}"
     * @return array
     */
    public static function postsPagedByKeyword($keyword, $tag, $pageSize = 20, $pageTpl = '')
    {
        $where                     = [];
        $where['posts.post_title'] = ['like', "%$keyword%"];

        $content = self::posts($tag, $where, $pageSize, $pageTpl);

        return $content;
    }

    /**
     * 获取指定id的文章
     * @param int $postId posts表下的id.
     * @param string $tag 查询标签,以字符串方式传入,例："field:post_title,post_content;"<br>
     *    field:调用post指定字段,如(id,post_title...) 默认全部<br>
     * @return array 返回指定id的文章
     */
    public static function post($postId, $tag)
    {
        $where = [];

        $tag   = sp_param_lable($tag);
        $field = !empty($tag['field']) ? $tag['field'] : '*';

        $where['post_status'] = ['eq', 1];
        $where['id']          = ['eq', $postId];

        $post = M('Posts')->field($field)->where($where)->find();

        return $post;
    }

    /**
     * 获取指定条件的页面列表
     * @param string $tag 查询标签,以字符串方式传入,例："ids:1,2;field:post_title,post_content;limit:0,8;order:post_date desc,listorder desc;where:id>0;"<br>
     *    ids:调用指定id的一个或多个数据,如 1,2,3<br>
     *    field:调用post指定字段,如(id,post_title...) 默认全部<br>
     *    limit:数据条数,默认值为10,可以指定从第几条开始,如0,8(表示共调用8条,从第1条开始)<br>
     *    order:排序方式,如：post_date desc<br>
     *    where:查询条件,字符串形式,和sql语句一样
     * @param array $where 查询条件(只支持数组),格式和thinkPHP的where方法一样；
     * @return array 返回符合条件的所有页面
     */
    public static function pages($tag, $where = [])
    {
        if (!is_array($where)) {
            $where = [];
        }
        $tag   = sp_param_lable($tag);
        $field = !empty($tag['field']) ? $tag['field'] : '*';
        $limit = !empty($tag['limit']) ? $tag['limit'] : '0,10';
        $order = !empty($tag['order']) ? $tag['order'] : 'post_date DESC';

        //根据参数生成查询条件
        $where['post_status'] = ['eq', 1];
        $where['post_type']   = ['eq', 2];

        if (isset($tag['ids'])) {
            $tag['ids']  = explode(',', $tag['ids']);
            $tag['ids']  = array_map('intval', $tag['ids']);
            $where['id'] = ['in', $tag['ids']];
        }

        if (isset($tag['where'])) {
            $where['_string'] = $tag['where'];
        }

        $postsModel = M("Posts");

        $pages = $postsModel->field($field)->where($where)->order($order)->limit($limit)->select();

        return $pages;
    }

    /**
     * 获取指定id的页面
     * @param int $id 页面的id
     * @return array 返回符合条件的页面
     */
    public static function page($id)
    {
        $where                = [];
        $where['id']          = ['eq', $id];
        $where['post_type']   = ['eq', 2];
        $where['post_status'] = ['eq', 1];

        $postsModel = M("Posts");
        $post       = $postsModel->where($where)->find();
        return $post;
    }

    /**
     * 返回指定分类
     * @param int $categoryId 分类id
     * @return array 返回符合条件的分类
     */
    public static function term($categoryId)
    {
        $terms = F('all_terms');
        if (empty($terms)) {
            $terms_model = M("Terms");
            $terms       = $terms_model->where("status=1")->select();
            $mTerms      = [];

            foreach ($terms as $t) {
                $tid             = $t['term_id'];
                $mTerms["t$tid"] = $t;
            }

            F('all_terms', $mTerms);
            return $mTerms["t$categoryId"];
        } else {
            return $terms["t$categoryId"];
        }
    }

    /**
     * 返回指定分类下的子分类
     * @param int $categoryId 分类id
     * @return array 返回指定分类下的子分类X
     */
    public static function childTerms($categoryId)
    {
        $categoryId  = intval($categoryId);
        $terms_model = M("Terms");
        $terms       = $terms_model->where("status=1 and parent=$categoryId")->order("listorder asc")->select();

        return $terms;
    }

    /**
     * 返回指定分类下的所有子分类
     * @param int $categoryId 分类id
     * @return array 返回指定分类下的所有子分类
     */
    public static function allChildTerms($categoryId)
    {
        $categoryId  = intval($categoryId);
        $terms_model = M("Terms");

        $terms = $terms_model->where("status=1 and path like '%-$categoryId-%'")->order("listorder asc")->select();

        return $terms;
    }

    /**
     * 返回符合条件的所有分类
     * @param string $tag 查询标签,以字符串方式传入,例："ids:1,2;field:term_id,name,description,seo_title;limit:0,8;order:path asc,listorder desc;where:term_id>0;"<br>
     *    ids:调用指定id的一个或多个数据,如 1,2,3
     *    field:调用terms表里的指定字段,如(term_id,name...) 默认全部,用*代表全部
     *    limit:数据条数,默认值为10,可以指定从第几条开始,如3,8(表示共调用8条,从第3条开始)
     *    order:排序方式,如：path desc,listorder asc<br>
     *    where:查询条件,字符串形式,和sql语句一样
     * @param array $where 查询条件(只支持数组),格式和thinkphp的where方法一样；
     * @return array 返回符合条件的所有分类
     *
     */
    public static function terms($tag, $where = [])
    {
        if (!is_array($where)) {
            $where = [];
        }

        $tag   = sp_param_lable($tag);
        $field = !empty($tag['field']) ? $tag['field'] : '*';
        $limit = !empty($tag['limit']) ? $tag['limit'] : '';
        $order = !empty($tag['order']) ? $tag['order'] : 'term_id';

        //根据参数生成查询条件
        $where['status'] = ['eq', 1];

        if (isset($tag['ids'])) {
            $tag['ids']       = explode(',', $tag['ids']);
            $tag['ids']       = array_map('intval', $tag['ids']);
            $where['term_id'] = ['in', $tag['ids']];
        }

        if (isset($tag['where'])) {
            $where['_string'] = $tag['where'];
        }

        $terms_model = M("Terms");
        $terms       = $terms_model->field($field)->where($where)->order($order)->limit($limit)->select();
        return $terms;
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

    /**
     * 获取所有友情链接
     */
    public static function links()
    {
        return Db::name('link')->where('status', 1)->order('list_order ASC')->select();
    }


    /**
     * 获取所有幻灯片
     * @param $slideId
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function slides($slideId)
    {
        $slideCount = Db::name('slide')->where('id', $slideId)->where('status', 1)->count();

        if ($slideCount == 0) {
            return [];
        }

        $slides = Db::name('slide_item')->where('status', 1)->where('slide_id', $slideId)->order('list_order ASC')->select();

        return $slides;
    }
}