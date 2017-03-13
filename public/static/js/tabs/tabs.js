/*!
 * PHPWind UI Library 
 * Wind.tabs 选项卡组件
 * Author: chaoren1641@gmail.com
 * <ul class="tabs_nav">
 * 	   <li><a href="ahah_1.html">Content 1</a></li>
 * 	   <li><a href="ahah_2.html">Content 2</a></li>
 * 	   <li><a href="ahah_3.html">Content 3</a></li>
 * </ul>
 * <div class="tabs_content">
 * 	 <div>Content1</div>
 * 	 <div>Content2</div>
 * 	 <div>Content3</div>
 * </div>
 * $('.tabs_nav').tabs(.tabs_content > div);
 */
;(function ( $, window, document, undefined ) {
    var pluginName = 'tabs',
        defaults = {
            activeClass		: 'current',//当前激活的选项卡样式
            event			: 'click',//触发事件
            fx				: 0,//显示时的动画，支持jQuery动画
            selected		: 0, //默认选中项
            onShow          : $.noop
        };
        
    function Plugin( element, selector, options ) {
        this.element = element;
        this.selector = selector;
        this.options = $.extend( {}, defaults, options) ;
        this.init();
    }

    Plugin.prototype.init = function () {
    	var element = this.element,
    		selector = this.selector,
            options = this.options,
          	navList = element.children(),
          	contentList = $(selector);
        
        //WAI-ARIA无障碍
        element.attr('role','tablist');
        navList.attr('role','tab');
        contentList.attr({'role':'tabpanel','aria-hidden':'true'});
        
    	function show(index) {
    		var selected_element = navList.eq(index);
    		selected_element.addClass( options.activeClass ).siblings().removeClass( options.activeClass );
            var currentContent = contentList.eq(index);
    		currentContent.show( options.fx ,options.onShow.call(currentContent)).attr('aria-hidden','false').siblings().hide( options.fx ).attr('aria-hidden','true');
    	}

    	show(options.selected);

    	//add event
    	navList.bind(options.event,function(e) { 
    		e.preventDefault();
    		e.stopPropagation();
    		var index = $(this).index();
    		show(index);
    	});
    	navList.find(' > a').bind('focus',function(e) {
    		e.stopPropagation();
    		e.preventDefault();
    		$(this).parent().trigger(options.event);
    	});
    };

    $.fn[pluginName] = function ( selector, options ) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName, new Plugin( $(this), selector ,options ));
            }
        });
    }

})( jQuery, window );
