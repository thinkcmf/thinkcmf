/**
 * @author jianqiang3@staff.sina.com.cn
 */
(function(){
	
	var count = 0, timer, callback;
	
	var $E = function(id) {
        if (typeof id === 'string') {
            return document.getElementById(id);
        }
        else {
            return id;
        }
    };
	
	function trim(str) {
		if(typeof str !== 'string'){
			throw 'trim need a string as parameter';
		}
		if(typeof str.trim === 'function'){
			return str.trim();
		}else{
			return str.replace(/^(\u3000|\s|\t|\u00A0)*|(\u3000|\s|\t|\u00A0)*$/g, '');
		}
	}
	
	function addEvent (sNode, sEventType, oFunc) {
        var oElement = $E(sNode);
        if (oElement == null) {
            return false;
        }
        sEventType = sEventType || 'click';
        if ((typeof oFunc).toLowerCase() != "function") {
            return;
        }
        if (oElement.attachEvent) {
            oElement.attachEvent('on' + sEventType, oFunc);
        }
        else if (oElement.addEventListener) {
            oElement.addEventListener(sEventType, oFunc, false);
        }
        else {
            oElement['on' + sEventType] = oFunc;
        }
        return true;
	}
	
	/**
	 * query to json 
	 * @param {Object} QS
	 * @param {Object} isDecode
	 */
	function queryToJson(QS, isDecode) {
		var _Qlist = trim(QS).split("&");
		var _json  = {};
		var _fData = function(data){
			if(isDecode){
				return decodeURIComponent(data);
			}else{
				return data;
			}
		};
		for(var i = 0, len = _Qlist.length; i < len; i++){
			if(_Qlist[i]){
				_hsh = _Qlist[i].split("=");
				_key = _hsh[0];
				_value = _hsh[1];
				
				// 如果只有key没有value, 那么将全部丢入一个$nullName数组中
				if(_hsh.length < 2){
					_value = _key;
					_key = '$nullName';
				}
				// 如果缓存堆栈中没有这个数据
				if(!_json[_key]) {
					_json[_key] = _fData(_value);
				}
				// 如果堆栈中已经存在这个数据，则转换成数组存储
				else {
					if($.core.arr.isArray(_json[_key]) != true) {
						_json[_key] = [_json[_key]];
					}
					_json[_key].push(_fData(_value));
				}
			}
		}
		return _json;
	}
	
	function jsonToQuery(JSON,isEncode) {
		var _fdata = function(data,isEncode){
			data = data == null? '': data;
			data = trim(data.toString());
			if(isEncode){
				return encodeURIComponent(data);
			}else{
				return data;
			}
		};
		
		var _Qstring = [];
		if(typeof JSON == "object"){
			for(var k in JSON){
				if(JSON[k] instanceof Array){
					for(var i = 0, len = JSON[k].length; i < len; i++){
						_Qstring.push(k + "=" + _fdata(JSON[k][i],isEncode));
					}
				}else{
					if(typeof JSON[k] != 'function'){
						_Qstring.push(k + "=" +_fdata(JSON[k],isEncode));
					}
				}
			}
		}
		if(_Qstring.length){
			return _Qstring.join("&");
		}else{
			return "";
		}
	}
	
	/**
	 * 初始化监听程序
	 */
	var init = function(){
		if (window.postMessage) {
			addEvent(window, "message", messageHanlder);
	    } else {
			messagePoll(window, "name");
	    };
	};
	
	/**
	 * window.name监听方式
	 * @param {Object} oWindow
	 * @param {Object} sName
	 */
	var messagePoll = function(oWindow, sName) {
        var hash = "";
		/**
		 * 处理处理
		 * @param {Object} name	返回的window.name
		 */
        function parseData(name) {
            var oData = name.split("^").pop().split("&");
            return {
                domain: oData[0],	//域名
                data: window.unescape(oData[1])	//数据
            }
        }
		/**
		 * 获取window.name
		 */
        function getWinName() {
            var name = oWindow[sName]; //=window.name

            //如果和上次不一样，则获取新数据
            if (name != hash) {
                hash = name;
                messageHanlder(parseData(name));
            }
        }
        timer = setInterval(getWinName, 1000 / 20);
    };
	
	/**
	 * 消息监听函数
	 * @param {Object} oEvt	监听到的数据对象
	 */
    var messageHanlder = function(oEvt) {
        oEvt = oEvt || window.event;
		var data = unescape(oEvt.data);
		var args = queryToJson(data);
		//执行回调
		callback && callback(args);
    };
	init();
	
	
	/*---------------------------公开函数------------------------------*/
	
	window.XD = {};
	/**
	 * 发送跨域消息
	 * @param {Object} target	//iframe或parent，如果为iframe，则需要传iframe.contentWindow对象
	 * @param {Object} oArgs
	 */
    XD.sendMessage = function(target, oArgs) {
		if(!target) return;
		if(typeof target != 'object') return;
		
		var data = jsonToQuery(oArgs);
			data = escape(data);
			
		if (window.postMessage) {
			// 此处一定要用“*”，否则数据不能返回，参考：http://dev.w3.org/html5/postmsg/#dom-window-postmessage
			target.postMessage(data, "*");
		} else {
			target.name = (new Date()).getTime() + (count++) + "^" + document.domain + "&" + window.escape(data);
		}
    };
	
	/**
	 * 接收跨域传递的消息，只需开发者重写即可
	 * @param {Object} data json数据
	 */
	XD.receiveMessage = function(oCallback) {
		callback = oCallback;
	};
	
})();