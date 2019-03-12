var $taskContentInner = null;
var $mainIframe       = null;
var tabwidth          = 118;
var $loading          = null;
var $navWraper        = $("#nav-wrapper");
var $content;
$(function () {
    $mainIframe      = $("#mainiframe");
    $content         = $("#content");
    $loading         = $("#loading");
    var headerHeight = 48;
    $content.height($(window).height() - headerHeight);

    $navWraper.height($(window).height() - 48 - 40);
    $navWraper.css("overflow", "auto");
    $(window).resize(function () {
        $navWraper.height($(window).height() - 48 - 40);
        $content.height($(window).height() - headerHeight);
        calcTaskContentWidth();
    });

    $("#content iframe").load(function () {
        $loading.hide();
    });

    $taskContentInner = $("#task-content-inner");

    $("#task-content-inner").on("click", "li", function () {
        openapp($(this).attr("app-url"), $(this).attr("app-id"), $(this).attr("app-name"));
        return false;
    });

    $("#task-content-inner").on("dblclick", "li", function () {
        closeapp($(this));
        return false;

    });
    $("#task-content-inner").on("click", ".cmf-component-tabclose", function () {
        closeapp($(this).parent());
        return false;
    });

    $("#task-next").click(function () {
        var marginLeft   = $taskContentInner.css("margin-left");
        marginLeft       = marginLeft.replace("px", "");
        var contentInner = $("#task-content-inner").width();
        var contentWidth = $("#task-content").width();
        var lessWidth    = contentWidth - contentInner;
        marginLeft       = marginLeft - tabwidth <= lessWidth ? lessWidth : marginLeft - tabwidth;

        $taskContentInner.stop();
        $taskContentInner.animate({"margin-left": marginLeft + "px"}, 300, 'swing');
    });

    $("#task-pre").click(function () {
        var marginLeft = $taskContentInner.css("margin-left");
        marginLeft     = parseInt(marginLeft.replace("px", ""));
        marginLeft     = marginLeft + tabwidth > 0 ? 0 : marginLeft + tabwidth;
        // $taskContentInner.css("margin-left", marginLeft + "px");
        $taskContentInner.stop();
        $taskContentInner.animate({"margin-left": marginLeft + "px"}, 300, 'swing');
    });

    $("#refresh-wrapper").click(function () {
        var $currentIframe = $("#content iframe:visible");
        $loading.show();
        //$currentIframe.attr("src",$currentIframe.attr("src"));
        $currentIframe[0].contentWindow.location.reload();
        return false;
    });

    //一键关闭顶部打开的菜单
    $("#close-wrapper").click(function () {
        $("#task-content-inner").children().each(function () {
            //保留首页。保留当前页面菜单。
            if ($(this).attr("app-id") != 0 && $(this).attr("class").indexOf("active") < 0) {
                $(this).remove();
            }
        });
        $("#content iframe:hidden").each(function () {
            //保留首页iframe
            if ($(this).attr("src").indexOf("/admin/main/index") < 0) {
                $(this).remove();
            }
        });
        calcTaskContentWidth();
        $("#task-next").click();
    });

    calcTaskContentWidth();
});

function calcTaskContentWidth() {
    var width = $("#task-content-inner").width();
    if (($(document).width() - 318 - tabwidth - 30 * 2) < width) {
        $("#task-content").width($(document).width() - 318 - tabwidth - 30 * 2);
        $("#task-next,#task-pre").show();
        $('#close-all-tabs-btn').show();
    } else {
        $("#task-next,#task-pre").hide();
        $('#close-all-tabs-btn').hide();
        $("#task-content").width(width);
    }
}

function close_current_app() {
    closeapp($("#task-content-inner .active"));
}

function closeapp($this) {
    if (!$this.is(".noclose")) {
        $this.prev().click();
        $this.remove();
        $("#appiframe-" + $this.attr("app-id")).remove();
        calcTaskContentWidth();
        $("#task-next").click();
    }

}


var task_item_tpl = '<li class="cmf-component-tabitem">' +
    '<a class="cmf-tabs-item-text"></a>' +
    '<span class="cmf-component-tabclose" href="javascript:void(0)" title="点击关闭标签"><span></span><b class="cmf-component-tabclose-icon">×</b></span>' +
    '</li>';

var appiframe_tpl = '<iframe style="width:100%;height: 100%;" frameborder="0" class="appiframe"></iframe>';

function openapp(url, appId, appname, refresh) {
    var $app = $("#task-content-inner li[app-id='" + appId + "']");
    $("#task-content-inner .active").removeClass("active");
    if ($app.length == 0) {
        var task = $(task_item_tpl).attr("app-id", appId).attr("app-url", url).attr("app-name", appname).addClass("active");
        task.find(".cmf-tabs-item-text").html(appname).attr("title", appname);
        $taskContentInner.append(task);
        $(".appiframe").hide();
        $loading.show();
        $appiframe = $(appiframe_tpl).attr("src", url).attr("id", "appiframe-" + appId);
        $appiframe.appendTo("#content");
        $appiframe.load(function () {
            var srcLoaded = $appiframe.get(0).contentWindow.location;
            if (srcLoaded.pathname == GV.ROOT) {
                window.location.reload(true);
            }
            $loading.hide();
        });
        calcTaskContentWidth();
    } else {
        $app.addClass("active");
        $(".appiframe").hide();
        var $iframe = $("#appiframe-" + appId);
        var src     = $iframe.get(0).contentWindow.location.href;
        src         = src.substr(src.indexOf("://") + 3);
        if (refresh === true) {//刷新
            $loading.show();
            $iframe.attr("src", url);
            $iframe.load(function () {
                var srcLoaded = $iframe.get(0).contentWindow.location;
                if (srcLoaded.pathname == GV.ROOT) {
                    window.location.reload(true);
                }
                $loading.hide();
            });
        }
        $iframe.show();
    }

    //url要添加参数。获取最外部的window.修改href
    // 支持History API
    if (window.history && history.pushState){
        var tw = window.top;

        var twa =tw.location.href.split("#");
        var newUrl =  twa[0]+"#"+url;
        tw.history.replaceState(null,null,newUrl);
    }



    var taskContentInner = $("#task-content-inner").width();
    var contentWidth     = $("#task-content").width();
    if (taskContentInner <= contentWidth) { //如果没有开始滚动就不用进行下去了
        return;
    }

    var currentTabIndex = $("#task-content-inner li[app-id='" + appId + "']").index();
    var itemOffset      = 0;
    var currentTabWidth = $("#task-content-inner li[app-id='" + appId + "']").width();

    $("#task-content-inner li:lt(" + currentTabIndex + ')').each(function () {
        itemOffset = itemOffset + $(this).width();
    });

    var cssMarginLeft = $taskContentInner.css("margin-left");

    cssMarginLeft = parseInt(cssMarginLeft.replace("px", ""));


    var marginLeft = currentTabWidth + itemOffset - contentWidth + cssMarginLeft;

    if (marginLeft > 0) {
        marginLeft = -(currentTabWidth + itemOffset - contentWidth);
        $taskContentInner.animate({"margin-left": marginLeft + "px"}, 300, 'swing');
        return;
    }

    if (itemOffset + cssMarginLeft < 0) {
        marginLeft = -itemOffset
        $taskContentInner.animate({"margin-left": marginLeft + "px"}, 300, 'swing');

        return;
    }


}

