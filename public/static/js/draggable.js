/*
 * PHPWind util Library
 * @Copyright 	: Copyright 2011, phpwind.com
 * @Descript	: 拖拽功能组件
 * @Author		: chaoren1641@gmail.com, wengqianshan@me.com
 * @Depend		: jquery.js(1.7 or later)
 * $Id: jquery.draggable.js 13814 2012-07-12 09:15:56Z chris.chencq $			:
 */
;(function ( $, window, document, undefined ) {
    var pluginName = 'draggable';
    var defaults = {
		handle	: '.handle',	// 要拖拽的手柄
		limit	: true
    };
	var lastMouseX,lastMouseY;

	//当前窗口内捕获鼠标操作
    function capture(elem) {
        elem.setCapture ? elem.setCapture() : window.captureEvents(Event.MOUSEMOVE | Event.MOUSEUP);
    }
    function release(elem) {
        elem.releaseCapture ? elem.releaseCapture() : window.releaseEvents(Event.MOUSEMOVE | Event.MOUSEUP);
    }
    function getMousePosition(e) {
		var posx = 0,
			posy = 0,
			db = document.body,
			dd = document.documentElement,
			e = e || window.event;

		if (e.pageX || e.pageY) {
			posx = e.pageX;
			posy = e.pageY;
		}
		else if (e.clientX || e.clientY) {
			posx = e.clientX + db.scrollLeft + dd.scrollLeft;
			posy = e.clientY + db.scrollTop  + dd.scrollTop;
		}
		return { 'x': posx, 'y': posy };
	}

	function Plugin(element,options) {
        this.element = element;
        this.options = $.extend( {}, defaults, options) ;
        this.handle = element.find(options.handle);
        this.init();
	}

    Plugin.prototype.init = function() {

    	var handle = this.handle,
    		options = this.options,
    		element = this.element,
    		winWidth,
			winHeight,
			docScrollTop = 0,
			docScrollLeft = 0,
    		POS_X = 0,
    		POS_Y = 0,
    		elemHeight = element.outerHeight(),
    		elemWidth = element.outerWidth();
    	handle.css({cursor:'move'});
    	var el = handle[0].setCapture ? handle : $(document);
        handle.on('mousedown',function(e) {
        	 if(options.mousedown) {
		        	options.mousedown(element);
		        }
        	if($.browser && $.browser.msie){
		        //设置鼠标捕获
		        handle[0].setCapture();
		    }else{
		        //焦点丢失
		        //$(window).blur();
		        //阻止默认动作
		        e.preventDefault();
		    };
        	capture(this);

        	//获取窗口尺寸和滚动条状态
		    winWidth = $(window).width();
		    winHeight = $(window).height();
		    docScrollTop = $(document).scrollTop();
		    docScrollLeft = $(document).scrollLeft();

        	//获取鼠标的初始偏移值
		    var offset = element.offset();
		    var mousePostion = getMousePosition(e);
		    //记录鼠标相对窗口的位置偏移
		    if(element.css('position') === 'fixed'){
		    	POS_X = mousePostion.x - offset.left + docScrollLeft;
		    	POS_Y = mousePostion.y - offset.top + docScrollTop;
		    }else{
		    	POS_X = mousePostion.x - offset.left;
		    	POS_Y = mousePostion.y - offset.top;
		    }

		    el.on('mousemove',function(e) {
		    	e.preventDefault();
		    	window.getSelection ? window.getSelection().removeAllRanges() : document.selection.empty();
		    	//获取鼠标位置、窗口目标位置和移动范围
	        	var mousePostion = getMousePosition(e),
	            	mouseY = mousePostion.y,
	            	mouseX = mousePostion.x,
		            currentLeft = mouseX - POS_X,
		            currentTop = mouseY - POS_Y;

			    //限制弹窗可移动范围，默认不超出可视区域
				if(options.limit){
					var maxLeft = winWidth - elemWidth + docScrollLeft,
			        	maxTop = winHeight - elemHeight + docScrollTop;
			        if(currentLeft < docScrollLeft){
			        	currentLeft = docScrollLeft;
			        }
			        if(currentTop < docScrollTop){
			        	currentTop = docScrollTop;
			        }
			        if(currentLeft > maxLeft){
			        	currentLeft = maxLeft;
			        }
			        if(currentTop > maxTop){
			        	currentTop = maxTop;
			        }
		        }
		        //设置窗口位置
		        element.css({ left : currentLeft + "px", top : currentTop + "px" });
		        if(options.mousemove) {
		        	options.mousemove(element, left, top);
		        }
       		}).on('mouseup.d keydown',function (e) {
       			//ESC停止拖拽
       			if(e.type === 'keydown' && e.keyCode !== 27) {
       				return;
       			}
	            release(this);
	            $(el).unbind('mousemove').unbind('mouseup.d');
	            if(options.mouseup) {
		        	options.mouseup(element);
		        }
			});
        });
	}

    $.fn[pluginName] = Wind[pluginName]= function ( options ) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName, new Plugin( $(this), options ));
            }
        });
    }

})( jQuery, window, document );
