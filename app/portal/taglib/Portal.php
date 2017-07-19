<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\portal\taglib;

use think\template\TagLib;

class Portal extends TagLib
{
    /**
     * 定义标签列表
     */
    protected $tags = [
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'articles'   => ['attr' => 'field,where,limit,order,page,relation,returnVarName,pageVarName,categoryIds', 'close' => 1],//非必须属性item
        'breadcrumb' => ['attr' => 'cid', 'close' => 1],//非必须属性self
    ];

    /**
     * 文章列表标签
     */
    public function tagArticles($tag, $content)
    {
        $item          = empty($tag['item']) ? 'vo' : $tag['item'];//循环变量名
        $field         = empty($tag['field']) ? '' : $tag['field'];
        $limit         = empty($tag['limit']) ? '10' : $tag['limit'];
        $order         = empty($tag['order']) ? 'post.published_time DESC' : $tag['order'];
        $relation      = empty($tag['relation']) ? '' : $tag['relation'];
        $pageVarName   = empty($tag['pageVarName']) ? '__PAGE_VAR_NAME__' : $tag['pageVarName'];
        $returnVarName = empty($tag['returnVarName']) ? 'articles_data' : $tag['returnVarName'];

        $where = '""';
        if (!empty($tag['where']) && strpos($tag['where'], '$') === 0) {
            $where = $tag['where'];
        }

        $page = "''";
        if (!empty($tag['page'])) {
            if (strpos($tag['page'], '$') === 0) {
                $page = $tag['page'];
            } else {
                $page = intval($tag['page']);
                $page = "'{$page}'";
            }
        }

        $categoryIds = "''";
        if (!empty($tag['categoryIds'])) {
            if (strpos($tag['categoryIds'], '$') === 0) {
                $categoryIds = $tag['categoryIds'];
                $this->autoBuildVar($categoryIds);
            } else {
                $categoryIds = "'{$tag['categoryIds']}'";
            }
        }

        $parse = <<<parse
<?php
\${$returnVarName} = \app\portal\service\ApiService::articles([
    'field'   => '{$field}',
    'where'   => {$where},
    'limit'   => '{$limit}',
    'order'   => '{$order}',
    'page'    => $page,
    'relation'=> '{$relation}',
    'category_ids'=>{$categoryIds}
]);

\${$pageVarName} = isset(\${$returnVarName}['page'])?\${$returnVarName}['page']:'';

 ?>
<volist name="{$returnVarName}.articles" id="{$item}">
{$content}
</volist>
parse;
        return $parse;
    }

    /**
     * 面包屑标签
     */
    public function tagBreadcrumb($tag, $content)
    {
        $cid = empty($tag['cid']) ? '0' : $tag['cid'];

        if (!empty($cid)) {
            $this->autoBuildVar($cid);
        }

        $self = isset($tag['self']) ? $tag['self'] : 'false';

        $parse = <<<parse
<?php
if(!empty({$cid})){
    \$__BREADCRUMB_ITEMS__ = \app\portal\service\ApiService::breadcrumb({$cid},{$self});
?>

<volist name="__BREADCRUMB_ITEMS__" id="vo">
    {$content}
</volist>

<?php
}
?>
parse;

        return $parse;

    }


}