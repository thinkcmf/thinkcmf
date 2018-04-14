/**
 * jQuery Server Plugin
 *
 * Server-side Ajax requests supporting jQuery manipulations
 * before sending content to the browser.
 * 
 * Example:
 * $.server({url: ${URL})
 * 	.find('.my-class')
 * 	.client(${CALLBACK});
 *
 * @version 0.5.1
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 * @link http://code.google.com/p/phpquery/wiki/jQueryServer
 * @link http://code.google.com/p/phpquery/
 */
jQuery.extend({
	serverConfig: function() {
		if (typeof jQueryServerConfig != 'undefined')
			return jQueryServerConfig;
		return {};
	}(),
	server: function(options){
		// set default url
		if (! jQuery.serverConfig.url)
			jQuery.serverConfig.url = jQuery('script[src$=jquery.js]')
				.attr('src').replace(/jquery\.js$/, '')
				+'jQueryServer.php';
		// this is cache object
		var objectCache = {};
		// dump all jQuery methods, but only once
		// $.each doesn't work ?
		for( var i in jQuery.fn) {
			// closure to preserve loop iterator in scope
			(function(){
				var name = i;
				// create dummy method
				objectCache[name] = function(){
					// create method data object
					var data = {
						method: name,
						arguments: []
					};
					// collect arguments
					$.each(arguments, function(k, v){
						data.arguments.push(v);
					});
					// push data into stack
					this.stack.push(data);
					// preserve chain
					return this;
				}
			})();
		}
		/**
		 * Fetches results from phpQuery.
		 * 
		 * @param {Function} callback	Optional. Turns on async request.
		 * First parameter for callback is usually an JSON array of mathed elements. Use $(result) to append it to DOM.
		 * It can also be a boolean value or string, depending on last method called.
		 */
		objectCache.client = function(success, error){
//			console.log(this.stack.toSource());
//			success = success || function(){
//				return $result;
//			};
			$.ajax({
				type: 'POST',
				data: {data: $.toJSON(this.stack)},
				async: false,
				// jQuery.server.config ???
				url: jQuery.serverConfig.url,
//				success: function(response){
//					var $result = jQuery();
//					$.each(response, function(v) {
//						$result.add(v);
//					})
//					success.call(null, $result);
//				},
//				success: success,
				success: function(response){
					if (options['dataType'] == 'json')
						response = $.parseJSON(response);
					success(response);
				},
				error: error
			})
		}
		// replace orginal method with generated method using cache (lazy-load)
		jQuery.server = function(options){
			// clone cache object
			var myCache = jQuery.extend({}, objectCache);
			myCache.stack = [options];
			return myCache;
		}
		// returen result from new method (only done for first call)
		return jQuery.server(options);
	}
});
// toJSON by Mark Gibson
if (typeof $.toJSON == 'undefined') {
	(function ($) {
	    var m = {
	            '\b': '\\b',
	            '\t': '\\t',
	            '\n': '\\n',
	            '\f': '\\f',
	            '\r': '\\r',
	            '"' : '\\"',
	            '\\': '\\\\'
	        },
	        s = {
	            'array': function (x) {
	                var a = ['['], b, f, i, l = x.length, v;
	                for (i = 0; i < l; i += 1) {
	                    v = x[i];
	                    f = s[typeof v];
	                    if (f) {
	                        v = f(v);
	                        if (typeof v == 'string') {
	                            if (b) {
	                                a[a.length] = ',';
	                            }
	                            a[a.length] = v;
	                            b = true;
	                        }
	                    }
	                }
	                a[a.length] = ']';
	                return a.join('');
	            },
	            'boolean': function (x) {
	                return String(x);
	            },
	            'null': function (x) {
	                return "null";
	            },
	            'number': function (x) {
	                return isFinite(x) ? String(x) : 'null';
	            },
	            'object': function (x) {
	                if (x) {
	                    if (x instanceof Array) {
	                        return s.array(x);
	                    }
	                    var a = ['{'], b, f, i, v;
	                    for (i in x) {
	                        v = x[i];
	                        f = s[typeof v];
	                        if (f) {
	                            v = f(v);
	                            if (typeof v == 'string') {
	                                if (b) {
	                                    a[a.length] = ',';
	                                }
	                                a.push(s.string(i), ':', v);
	                                b = true;
	                            }
	                        }
	                    }
	                    a[a.length] = '}';
	                    return a.join('');
	                }
	                return 'null';
	            },
	            'string': function (x) {
	                if (/["\\\x00-\x1f]/.test(x)) {
	                    x = x.replace(/([\x00-\x1f\\"])/g, function(a, b) {
	                        var c = m[b];
	                        if (c) {
	                            return c;
	                        }
	                        c = b.charCodeAt();
	                        return '\\u00' +
	                            Math.floor(c / 16).toString(16) +
	                            (c % 16).toString(16);
	                    });
	                }
	                return '"' + x + '"';
	            }
	        };
	
		$.toJSON = function(v) {
			var f = isNaN(v) ? s[typeof v] : s['number'];
			if (f) return f(v);
		};
		
		$.parseJSON = function(v, safe) {
            if (JSON)
                return JSON.parse(v);
			if (safe === undefined)
                safe = $.parseJSON.safe;
			if (safe && !/^("(\\.|[^"\\\n\r])*?"|[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t])+?$/.test(v))
				return undefined;
			return eval('('+v+')');
		};
		
		$.parseJSON.safe = false;
	
	})(jQuery);
}