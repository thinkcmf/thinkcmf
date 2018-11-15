/*!
 * jQuery infiniteScroll v1.0.0
 * 无限滚动插件
 * http://www.thinkcmf.com
 * MIT License
 * by Dean(老猫)
 *
 */
// examples
// $('#nextpage').infiniteScroll({
//     total_pages:5,
//     pageParam:'page',
//     loading:'.js-infinite-scroll-loading',
//     success:function(content){
//         var $items=$(content).find('#container .item');
//         if($items.length>0){
//             $container.append( $items );
//         }
//     },
//     finish:function(){
//
//     }
// });
;(function ($) {
    $.fn.infiniteScroll          = function (options) {
        var opts     = $.extend({}, $.fn.infiniteScroll.defaults, options);
        var url      = location.href;
        var $loading = $(opts.loading);
        $loading.hide();
        return this.each(function () {
            var $document = $(document);
            var $window   = $(window);
            var $this     = $(this);
            var page      = opts.page;

            function _loadData() {
                if ($this.data('loading')) {
                    return;
                }
                $this.data('loading', true);
                $loading.show();
                var data = {};
                page++;
                if (page > opts.total_pages) {
                    $loading.hide();
                    opts.finish();
                    return;
                }
                data[opts.pageParam] = page;
                opts.startLoading();
                $.ajax({
                    url: url,
                    data: data,
                    type: 'GET',
                    dateType: 'html',
                    success: function (content) {
                        opts.success(content, page);
                    },
                    error: function () {
                        opts.error();
                    },
                    complete: function () {
                        $loading.hide();
                        $this.data('loading', false);
                    }
                });
            }

            if (opts.trigger == 'scroll') {
                $(window).scroll(function () {
                    if ($this.data('loading') || $this.is(':hidden')) return;
                    if ($document.scrollTop() > $this.position().top - $window.height()) {
                        _loadData();
                    }
                });
            }

            if (opts.trigger == 'click') {
                $this.click(function () {
                    if ($this.data('loading') || $this.is(':hidden')) return;

                    _loadData();
                });
            }


        });
    };
    $.fn.infiniteScroll.defaults = {
        pageParam: 'page',
        loading: '.js-infinite-scroll-loading',
        page: 1,
        trigger: 'scroll',//scroll,click
        success: function () {
        },
        finish: function () {
        },
        error: function () {
        },
        startLoading: function () {
            //
        },
        complete: function () {
            // 数据加载完成
        }

    };
})(jQuery); 