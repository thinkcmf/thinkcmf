<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace cmf\lib\taglib;

use think\template\TagLib;

class Cmf extends TagLib
{
    /**
     * 定义标签列表
     */
    protected $tags = [
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'page'                => ['attr' => '', 'close' => 0],//非必须属性name
        'widget'              => ['attr' => '', 'close' => 1],
        'navigation'          => ['attr' => '', 'close' => 1],//非必须属性nav-id,root,id,class
        'navigationmenu'      => ['attr' => '', 'close' => 1],//root,class
        'navigationfolder'    => ['attr' => '', 'close' => 1],//root,class,dropdown,dropdown-class
        'subnavigation'       => ['attr' => 'parent,root,id,class', 'close' => 1],
        'subnavigationmenu'   => ['attr' => '', 'close' => 1],//root,class
        'subnavigationfolder' => ['attr' => '', 'close' => 1],//root,class,dropdown,dropdown-class
        'links'               => ['attr' => '', 'close' => 1],//非必须属性item
        'slides'              => ['attr' => 'id', 'close' => 1],//非必须属性item
        'noslides'            => ['attr' => 'id', 'close' => 1],
        'captcha'             => ['attr' => 'height,width', 'close' => 0],//非必须属性font-size,length,bg,id
        'hook'                => ['attr' => 'name,param,once', 'close' => 0],
        'tree'                => ['attr' => 'name', 'close' => 1],
        'css'                 => ['attr' => '', 'close' => 0],//非必须属性name
        'js'                  => ['attr' => '', 'close' => 0],//非必须属性name
    ];

    /**
     * 分页标签
     */
    public function tagPage($tag, $content)
    {

        $name = isset($tag['name']) ? $tag['name'] : '__PAGE_VAR_NAME__';
        $this->autoBuildVar($name);

        $parse = <<<parse
<?php
     echo empty({$name})?'':{$name};
 ?>
parse;

        return $parse;

    }

    /**
     * 组件标签
     */
    public function tagWidget($tag, $content)
    {

        if (empty($tag['name'])) {
            $designingTheme = cookie('cmf_design_theme');
            $name           = '';
            $tagName        = '';
            $attrsText      = '';
            if (!empty($tag['tag'])) {
                $tagName = $tag['tag'];
                if (strpos($tagName, '$') === 0) {
                    $this->autoBuildVar($tagName);
                } else {
                    $tagName = "{$tagName}";
                }

                $attrsText = '';

                unset($tag['tag']);
                unset($tag['name']);
                $attrs = [];

                if ($designingTheme) {
                    if (!isset($tag['class'])) {
                        $attrs[] = 'class="__cmf_widget_in_block"';
                    }

                    $attrs[] = 'data-cmf_theme_file_id="<?php echo $_theme_file_id;?>"';
                    $attrs[] = 'data-cmf_widget_id="<?php echo $_widget_id;?>"';
                }

                if (!isset($tag['style'])) {
                    $tag['style'] = '';
                }


                foreach ($tag as $attrName => $attrValue) {
                    if (strpos($attrValue, '$') === 0) {
                        $this->autoBuildVar($attrValue);
                        $attrValue = "<?php echo $attrValue ?>";
                    } else {
                        $attrValue = "{$attrValue}";
                    }

                    if ($attrName == 'class' && $designingTheme) {
                        $attrValue = '__cmf_widget_in_block ' . $attrValue;
                    }

                    if ($attrName == 'style') {
                        $styles = <<<hello
<?php 
if(isset(\$widget['css'])){
    foreach(\$widget['css'] as \$cssAttrName=>\$cssValue){
        echo \$cssAttrName.':'.\$cssValue.';';
    }
}
?>
hello;

                        $attrValue = $attrValue . ';' . str_replace("\n", '', $styles);
                    }

                    $attrs[] = $attrName . '="' . $attrValue . '"';
                }

                $attrsText = ' ' . join(' ', $attrs);

            } else {
                throw new \Exception('请给控件设置tag属性');
            }


        } else {
            $name = $tag['name'];
            if (strpos($name, '$') === 0) {
                $this->autoBuildVar($name);
            } else {
                $name = "'{$name}'";
            }
        }


        if (empty($name)) {
            $parse = <<<parse
<$tagName{$attrsText}>
{$content}
</$tagName>
parse;
        } else {
            $parse = <<<parse
<?php
     if((isset(\$theme_widgets[{$name}]) && \$theme_widgets[{$name}]['display'])){
        \$widget=\$theme_widgets[{$name}];
 ?>
{$content}
<?php
    }
 ?>
parse;
        }

        return $parse;

    }

    /**
     * 导航标签
     */
    public function tagNavigation($tag, $content)
    {

        // nav-id,id,root,class
        $navId                   = isset($tag['nav-id']) ? $tag['nav-id'] : 0;
        $id                      = isset($tag['id']) ? $tag['id'] : '';
        $root                    = isset($tag['root']) ? $tag['root'] : 'ul';
        $class                   = isset($tag['class']) ? $tag['class'] : 'nav navbar-nav';
        $maxLevel                = isset($tag['max-level']) ? intval($tag['max-level']) : 0;
        $parseNavigationFuncName = '__parse_navigation_' . md5($navId . $id . $class);

        if (strpos($navId, '$') === 0) {
            $this->autoBuildVar($navId);
        } else {
            $navId = "'{$navId}'";
        }

        $parse = <<<parse
<?php
/*start*/
if (!function_exists('{$parseNavigationFuncName}')) {
    function {$parseNavigationFuncName}(\$menus,\$level=1){
        \$_parse_navigation_func_name = '{$parseNavigationFuncName}';
?>
        <foreach name="menus" item="menu">
        {$content}
        </foreach>
        
<?php 
    }
}
/*end*/
?>

<?php
    \$navMenuModel = new \app\admin\model\NavMenuModel();
    \$menus = \$navMenuModel->navMenusTreeArray({$navId},{$maxLevel});
?>
<if condition="'{$root}'==''">
    {:{$parseNavigationFuncName}(\$menus)}
<else/>
    <{$root} id="{$id}" class="{$class}">
        {:{$parseNavigationFuncName}(\$menus)}
    </$root>
</if>

parse;
        return $parse;
    }

    /**
     * 导航menu标签
     */
    public function tagNavigationMenu($tag, $content)
    {
        //root,class
        $root  = empty($tag['root']) ? '' : $tag['root'];
        $class = empty($tag['class']) ? '' : $tag['class'];

        if (empty($root)) {
            $parse = <<<parse
<if condition="empty(\$menu['children'])">
    {$content}
</if>
parse;
        } else {
            $parse = <<<parse
<if condition="empty(\$menu['children'])">
    <{$root} class="{$class}">
    {$content}
    </{$root}>
</if>
parse;
        }

        return $parse;
    }


    /**
     * 导航folder标签
     */
    public function tagNavigationFolder($tag, $content)
    {
        //root,class,dropdown,dropdown-class
        $root          = empty($tag['root']) ? 'li' : $tag['root'];
        $class         = empty($tag['class']) ? 'dropdown' : $tag['class'];
        $dropdown      = isset($tag['dropdown']) ? $tag['dropdown'] : 'ul';
        $dropdownClass = isset($tag['dropdown-class']) ? $tag['dropdown-class'] : 'dropdown-menu';

        $parse = <<<parse
<if condition="!empty(\$menu['children'])">
    <{$root} class="{$class}">
        {$content}
        <{$dropdown} class="{$dropdownClass}">
            <php>
            \$mLevel=\$level+1;
            </php>
            <php>echo \$_parse_navigation_func_name(\$menu['children'],\$mLevel);</php>
        </{$dropdown}>
    </{$root}>
</if>
parse;

        return $parse;
    }

    /**
     * 子导航标签
     */
    public function tagSubNavigation($tag, $content)
    {

        // parent,id,root,class
        $parent                     = isset($tag['parent']) ? $tag['parent'] : 0;
        $id                         = isset($tag['id']) ? $tag['id'] : '';
        $root                       = isset($tag['root']) ? $tag['root'] : 'ul';
        $class                      = isset($tag['class']) ? $tag['class'] : 'nav navbar-nav';
        $maxLevel                   = isset($tag['max-level']) ? intval($tag['max-level']) : 0;
        $parseSubNavigationFuncName = '__parse_sub_navigation_' . md5($id . $class);

        if (strpos($parent, '$') === 0) {
            $this->autoBuildVar($parent);
        } else {
            $parent = "'{$parent}'";
        }

        $parse = <<<parse
<?php
if (!function_exists('{$parseSubNavigationFuncName}')) {
    function {$parseSubNavigationFuncName}(\$menus,\$level=1){
        \$_parse_sub_navigation_func_name = '{$parseSubNavigationFuncName}';
?>
        <foreach name="menus" item="menu">
        {$content}
        </foreach>
<?php 
    }
}
?>

<?php
    \$navMenuModel = new \app\admin\model\NavMenuModel();
    \$menus = \$navMenuModel->subNavMenusTreeArray({$parent},{$maxLevel});
?>
<if condition="'{$root}'==''">
    {:{$parseSubNavigationFuncName}(\$menus)}
<else/>
    <{$root} id="{$id}" class="{$class}">
        {:{$parseSubNavigationFuncName}(\$menus)}
    </$root>
</if>

parse;
        return $parse;
    }

    /**
     * 子导航menu标签
     */
    public function tagSubNavigationMenu($tag, $content)
    {
        //root,class
        $root  = !empty($tag['root']) ? $tag['root'] : '';
        $class = !empty($tag['class']) ? $tag['class'] : '';

        if (empty($root)) {
            $parse = <<<parse
<if condition="empty(\$menu['children'])">
    {$content}
</if>
parse;
        } else {
            $parse = <<<parse
<if condition="empty(\$menu['children'])">
    <{$root} class="{$class}">
    {$content}
    </{$root}>
</if>
parse;
        }

        return $parse;
    }

    /**
     * 子导航folder标签
     */
    public function tagSubNavigationFolder($tag, $content)
    {
        //root,class,dropdown,dropdown-class
        $root          = isset($tag['root']) ? $tag['root'] : 'li';
        $class         = isset($tag['class']) ? $tag['class'] : 'dropdown';
        $dropdown      = isset($tag['dropdown']) ? $tag['dropdown'] : 'dropdown';
        $dropdownClass = isset($tag['dropdown-class']) ? $tag['dropdown-class'] : 'dropdown-menu';

        $parse = <<<parse
<if condition="!empty(\$menu['children'])">
    <{$root} class="{$class}">
        {$content}
        <{$dropdown} class="{$dropdownClass}">
            <php>\$mLevel=\$level+1;</php>
            <php>echo \$_parse_sub_navigation_func_name(\$menu['children'],\$mLevel);</php>
        </{$dropdown}>
    </{$root}>
</if>
parse;
        return $parse;
    }

    /**
     * 友情链接标签
     */
    public function tagLinks($tag, $content)
    {
        $item  = empty($tag['item']) ? 'vo' : $tag['item'];//循环变量名
        $parse = <<<parse
<?php
     \$__LINKS__ = \app\admin\service\ApiService::links();
?>
<volist name="__LINKS__" id="{$item}">
{$content}
</volist>
parse;

        return $parse;

    }

    /**
     * 幻灯片标签
     */
    public function tagSlides($tag, $content)
    {
        $id = empty($tag['id']) ? '0' : $tag['id'];
        if (strpos($id, '$') === 0) {
            $this->autoBuildVar($id);
        }
        $item  = empty($tag['item']) ? 'vo' : $tag['item'];//循环变量名
        $parse = <<<parse
<?php
     \$__SLIDE_ITEMS__ = \app\admin\service\ApiService::slides({$id});
?>
<volist name="__SLIDE_ITEMS__" id="{$item}">
{$content}
</volist>
parse;

        return $parse;

    }

    /**
     * 无幻灯片标签
     */
    public function tagNoSlides($tag, $content)
    {
        $id = empty($tag['id']) ? '0' : $tag['id'];
        if (strpos($id, '$') === 0) {
            $this->autoBuildVar($id);
        }
        $parse = <<<parse
<?php
    if(!isset(\$__SLIDE_ITEMS__)){
        \$__SLIDE_ITEMS__ = \app\admin\service\ApiService::slides({$id});
    }
?>
<if condition="count(\$__SLIDE_ITEMS__) eq 0">
{$content}
</if>
parse;

        return $parse;

    }

    public function tagCaptcha($tag, $content)
    {
        //height,width,font-size,length,bg,id
        $id       = empty($tag['id']) ? '' : $tag['id'];
        $paramId  = empty($tag['id']) ? '' : '&id=' . $tag['id'];
        $height   = empty($tag['height']) ? '' : '&height=' . $tag['height'];
        $width    = empty($tag['width']) ? '' : '&width=' . $tag['width'];
        $fontSize = empty($tag['font-size']) ? '' : '&font_size=' . $tag['font-size'];
        $length   = empty($tag['length']) ? '' : '&length=' . $tag['length'];
        $bg       = empty($tag['bg']) ? '' : '&bg=' . $tag['bg'];
        $title    = empty($tag['title']) ? '换一张' : $tag['title'];
        $style    = empty($tag['style']) ? 'cursor: pointer;' : $tag['style'];
        $params   = ltrim("{$paramId}{$height}{$width}{$fontSize}{$length}{$bg}", '&');
        $parse    = <<<parse
<php>\$__CAPTCHA_SRC=url('/new_captcha').'?{$params}';</php>
<img src="{\$__CAPTCHA_SRC}" onclick="this.src='{\$__CAPTCHA_SRC}&time='+Math.random();" title="{$title}" class="captcha captcha-img verify_img" style="{$style}"/>{$content}
<input type="hidden" name="_captcha_id" value="{$id}">
parse;
        return $parse;
    }

    public function tagHook($tag, $content)
    {
        $name  = empty($tag['name']) ? '' : $tag['name'];
        $param = empty($tag['param']) ? '' : $tag['param'];
        $once  = empty($tag['once']) ? 'false' : 'true';

        if (empty($param)) {
            $param = 'null';
        } else if (strpos($param, '$') === false) {
            $this->autoBuildVar($param);
        }

        $parse = <<<parse
<php>
    hook('{$name}',{$param},{$once});
</php>
parse;
        return $parse;
    }


    public function tagTree($tag, $content)
    {
        $name = isset($tag['name']) ? $tag['name'] : 'items';
        $item = isset($tag['item']) ? $tag['item'] : 'vo';

        $parse = <<<parse
<php>
\$___tree= new \\tree\Tree();
\$___tree->init(\${$name});
\${$name}=\$___tree->createTree();
foreach (\${$name} as \$___node) {
    \$___stack           = [];
    \$___node['_level']  = 1;
    \$___node['_spacer'] = '';
    array_push(\$___stack, \$___node);
    \${$item} = [];
    while (count(\$___stack) > 0) {
        \${$item} = array_pop(\$___stack);
        if (!\${$item}) return;
</php>
{$content}
<php>
        if (!empty(\${$item}['children'])) {
            \$___childrenCount = count(\${$item}['children']);
            for (\$i = \$___childrenCount - 1; \$i >= 0; \$i--) {
                \${$item}['children'][\$i]['_level'] = \${$item}['_level'] + 1;
                if (\$i == \$___childrenCount - 1) {
                    \${$item}['children'][\$i]['_is_last'] = 1;
                    \${$item}['children'][\$i]['_spacer'] = str_repeat(\$___tree->nbsp, \${$item}['children'][\$i]['_level'] - 1). \$___tree->icon[2] . ' ';
                } else {
                    \${$item}['children'][\$i]['_is_last'] = 0;
                    \${$item}['children'][\$i]['_spacer'] = str_repeat(\$___tree->nbsp, \${$item}['children'][\$i]['_level'] - 1). \$___tree->icon[1] . ' ';
                }
                array_push(\$___stack, \${$item}['children'][\$i]);
            }
        }
    }
}
</php>
parse;

        return $parse;
    }

    /**
     * css标签
     */
    public function tagCss($tag, $content)
    {
        $href = isset($tag['href']) ? $tag['href'] : $tag['file'];
        if (strpos($href, '$') === 0) {
            $this->autoBuildVar($href);
        } else {
            $href = "'{$href}'";
        }

        $parse = <<<parse
<?php
if(!isset(\$_theme_css_href_list)){
    \$_theme_css_href_list=[];
}
if(!isset(\$_theme_css_href_list[{$href}])){
    \$_theme_css_href_list[{$href}]={$href};
?>
<link href="<?php echo $href;?>" rel="stylesheet">
<?php
}
?>
parse;

        return $parse;

    }

    /**
     * js标签
     */
    public function tagJs($tag, $content)
    {
        $src = isset($tag['src']) ? $tag['src'] : $tag['file'];
        if (strpos($src, '$') === 0) {
            $this->autoBuildVar($src);
        } else {
            $src = "'{$src}'";
        }

        $type = isset($tag['type']) ? $tag['type'] : '';
        if (strpos($type, '$') === 0) {
            $this->autoBuildVar($type);
            $type = <<<hello
 type="<?php echo \$type;?>"
hello;

        } else {
            if (!empty($type)) {
                $type = <<<hello
 type="{$type}"
hello;
            }
        }

        $parse = <<<parse
<?php
if(!isset(\$_theme_js_src_list)){
    \$_theme_js_src_list=[];
}
if(!isset(\$_theme_js_src_list[{$src}])){
    \$_theme_js_src_list[{$src}]={$src};
?>
<script src="<?php echo $src;?>"$type></script>
<?php
}
?>
parse;

        return $parse;

    }

}
