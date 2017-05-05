$task_content_inner = null;
$mainiframe=null;
var tabwidth=118;
$loading=null;
$nav_wraper=$("#nav_wraper");
$(function () {
	$mainiframe=$("#mainiframe");
	$content=$("#content");
	$loading=$("#loading");
	var headerheight=86;
	$content.height($(window).height()-headerheight);
	
	
	$nav_wraper.height($(window).height()-45);
	$nav_wraper.css("overflow","auto");
	//$nav_wraper.niceScroll();
	$(window).resize(function(){
		$nav_wraper.height($(window).height()-45);
		$content.height($(window).height()-headerheight);
		 calcTaskitemsWidth();
	});
	$("#content iframe").load(function(){
    	$loading.hide();
    });
	
    $task_content_inner = $("#task-content-inner");
   

    $("#searchMenuKeyWord").keyup(function () {
        var wd = $(this).val();
        //searchedmenus
        var $tmp = $("<div></div>");
        if (wd != "") {
            $("#allmenus a:contains('" + wd + "')").each(
        function () {
            $clone = $(this).clone().prepend('<img src="/images/left/01/note.png">');

            $clone.wrapAll('<div class="menuitemsbig"></div>').parent().attr("onclick", $clone.attr("onclick")).appendTo($tmp);

        }
        );
            $("#searchedmenus").html($tmp.html());
            $("#searchedmenus").show();
            $("#allmenus").hide();
            $("#defaultstartmenu").hide();
            $("#allmenuslink .menu_item_linkbutton").html("返回");
            isAllDefault = false;
            // $("#searchedmenus").html($tmp).show();

        }

    });

    

    $("#appbox  li .delete").click(function (e) {
        $(this).parent().remove();
        return false;
    });

   

    ///

    $(".apps_container li").live("click", function () {
        var app = '<li><span class="delete" style="display:inline">×</span><img src="" class="icon"><a href="#" class="title"></a></li>';
        var $app = $(app);
        $app.attr("data-appname", $(this).attr("data-appname"));
        $app.attr("data-appid", $(this).attr("data-appid"));
        $app.attr("data-appurl", $(this).attr("data-appurl"));
        $app.find(".icon").attr("src", $(this).attr("data-icon"));
        $app.find(".title").html($(this).attr("data-appname"));
        $app.appendTo("#appbox");
        $("#appbox  li .delete").off("click");
        $("#appbox  li .delete").click(function () {
            $(this).parent().remove();
            return false;
        });
    });

    ///
    $("#tdshortcutsmor1").click(function () {
        $(".window").hide();
    });

    $(".task-item").live("click", function () {
        var appid = $(this).attr("app-id");
        var $app = $('#' + appid);
        showTopWindow($app);
    });

    $("#task-content-inner li").live("click", function () {
    	openapp($(this).attr("app-url"), $(this).attr("app-id"), $(this).attr("app-name"));
    	return false;
    });
    
    $("#task-content-inner li").live("dblclick", function () {
    	closeapp($(this));
    	return false;
    	
    });
    $("#task-content-inner a.macro-component-tabclose").live("click", function () {
    	closeapp($(this).parent());
        return false;
    });
    
    $("#task-next").click(function () {
        var marginleft = $task_content_inner.css("margin-left");
        marginleft = marginleft.replace("px", "");
        var width = $("#task-content-inner li").length * tabwidth;
        var content_width = $("#task-content").width();
        var lesswidth = content_width - width;
        marginleft = marginleft - tabwidth <= lesswidth ? lesswidth : marginleft - tabwidth;

        $task_content_inner.stop();
        $task_content_inner.animate({ "margin-left": marginleft + "px" }, 300, 'swing');
    });
    $("#task-pre").click(function () {
        var marginleft = $task_content_inner.css("margin-left");
        marginleft = parseInt(marginleft.replace("px", ""));
        marginleft = marginleft + tabwidth > 0 ? 0 : marginleft + tabwidth;
        // $task_content_inner.css("margin-left", marginleft + "px");
        $task_content_inner.stop();
        $task_content_inner.animate({ "margin-left": marginleft + "px" }, 300, 'swing');
    });
    
    $("#refresh_wrapper").click(function(){
    	var $current_iframe=$("#content iframe:visible");
    	$loading.show();
    	//$current_iframe.attr("src",$current_iframe.attr("src"));
    	$current_iframe[0].contentWindow.location.reload();
    	return false;
    });

    calcTaskitemsWidth();
});
function calcTaskitemsWidth() {
    var width = $("#task-content-inner li").length * tabwidth;
    $("#task-content-inner").width(width);
    if (($(document).width()-268-tabwidth- 30 * 2) < width) {
        $("#task-content").width($(document).width() -268-tabwidth- 30 * 2);
        $("#task-next,#task-pre").show();
    } else {
        $("#task-next,#task-pre").hide();
        $("#task-content").width(width);
    }
}

function close_current_app(){
	closeapp($("#task-content-inner .current"));
}

function closeapp($this){
	if(!$this.is(".noclose")){
		$this.prev().click();
    	$this.remove();
    	$("#appiframe-"+$this.attr("app-id")).remove();
    	calcTaskitemsWidth();
    	$("#task-next").click();
	}
	 
}





var task_item_tpl ='<li class="macro-component-tabitem">'+
'<span class="macro-tabs-item-text"></span>'+
'<a class="macro-component-tabclose" href="javascript:void(0)" title="点击关闭标签"><span></span><b class="macro-component-tabclose-icon">×</b></a>'+
'</li>';

var appiframe_tpl='<iframe style="width:100%;height: 100%;" frameborder="0" class="appiframe"></iframe>';

function openapp(url, appid, appname, refresh) {
    var $app = $("#task-content-inner li[app-id='"+appid+"']");
    $("#task-content-inner .current").removeClass("current");
    if ($app.length == 0) {
        var task = $(task_item_tpl).attr("app-id", appid).attr("app-url",url).attr("app-name",appname).addClass("current");
        task.find(".macro-tabs-item-text").html(appname).attr("title",appname);
        $task_content_inner.append(task);
        $(".appiframe").hide();
        $loading.show();
        $appiframe=$(appiframe_tpl).attr("src",url).attr("id","appiframe-"+appid);
        $appiframe.appendTo("#content");
        $appiframe.load(function(){
        	$loading.hide();
        });
        calcTaskitemsWidth();
    } else {
    	$app.addClass("current");
    	$(".appiframe").hide();
    	var $iframe=$("#appiframe-"+appid);
    	var src=$iframe.get(0).contentWindow.location.href;
    	src=src.substr(src.indexOf("://")+3);
    	/*if(src!=GV.HOST+url){
    		$loading.show();
    		$iframe.attr("src",url);
    		$appiframe.load(function(){
            	$loading.hide();
            });
    	}*/
    	if(refresh===true){//刷新
    		$loading.show();
    		$iframe.attr("src",url);
    		$iframe.load(function(){
            	$loading.hide();
            });
    	}
    	$iframe.show();
    	//$mainiframe.attr("src",url);
    }
    
    //
    var itemoffset= $("#task-content-inner li[app-id='"+appid+"']").index()* tabwidth;
    var width = $("#task-content-inner li").length * tabwidth;
   
    var content_width = $("#task-content").width();
    var offset=itemoffset+tabwidth-content_width;
    
    var lesswidth = content_width - width;
    
    var marginleft = $task_content_inner.css("margin-left");
   
    marginleft =parseInt( marginleft.replace("px", "") );
    var copymarginleft=marginleft;
    if(offset>0){
    	marginleft=marginleft>-offset?-offset:marginleft;
    }else{
    	marginleft=itemoffset+marginleft>=0?marginleft:-itemoffset;
    }
    
    if(-itemoffset==marginleft){
    	marginleft = marginleft + tabwidth > 0 ? 0 : marginleft + tabwidth;
    }
    
    //alert("cddd:"+(content_width-copymarginleft)+" dddd:"+(-itemoffset));
    if(content_width-copymarginleft-tabwidth==itemoffset){
    	marginleft = marginleft - tabwidth <= lesswidth ? lesswidth : marginleft - tabwidth;
    }
    
	$task_content_inner.animate({ "margin-left": marginleft + "px" }, 300, 'swing');
    
    
    
  
}

