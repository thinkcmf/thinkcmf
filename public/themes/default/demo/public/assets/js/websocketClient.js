//参考https://github.com/MacArthurJustin/vue-remote 修改
/**
 *var client=WsClient({host:"127.0.0.1",port:9502});
 *client.connect();//链接服务器
 *client.on('/index/index/index',function(data){console.log(data)}).emit('/index/index/index',"data")//on每次绑定都不会销毁
 *client.once('/index/index/index',function(data){console.log(data)}).emit('/index/index/index',"data")//once每次绑定，接收数据后就销毁当前接收方法
 */
(function webpackUniversalModuleDefinition(root, factory) {
    if (typeof exports === 'object' && typeof module === 'object')
        module.exports = factory();
    else if (typeof define === 'function' && define.amd)
        define([], factory);
    else if (typeof exports === 'object')
        exports["WsClient"] = factory();
    else
        root["WsClient"] = factory();
})(this, function () {
    var WsClient = function (options) {
        var Client       = null,
            Handlers     = Object.create(null),
            socketPump   = [],
            pumpInterval = null;

        options            = options || {};
        options.secure     = options.secure || false;
        options.host       = options.host || "localhost";
        options.port       = options.port || 8080;
        options.identifier = options.identifier || 'event';
        options.endpoint   = options.endpoint || '';
        options.camelCase  = options.camelCase || true;

        /**
         * Connect to Websocket Server
         */
        function connect() {
            Client = new WebSocket(`${(options.secure ? 'wss://' : 'ws://')}${options.host}${options.port ? ':' + options.port : ''}/${options.endpoint}`, options.protocol);

            Client.onopen    = openHandler;
            Client.onerror   = errorHandler;
            Client.onmessage = messageHandler;
            Client.onclose   = closeHandler
        }

        /**
         * Handle Server Connection Event
         *
         * @param {Event} open
         */
        function openHandler(open) {
            console.log("Connected to Web Server");
            console.log(open);

            if (options.openHandler) options.openHandler(open)
        }

        /**
         * Handle Server Errors
         *
         * @param {Event} error
         */
        function errorHandler(error) {
            console.log("Error occured");
            console.log(error);

            if (options.errorHandler) options.errorHandler(error)
        }

        /**
         * Handle Messages Returned from the Server
         *
         * @param {MessageEvent} message
         * @returns
         */
        function messageHandler(message) {
            var Json       = JSON.parse(message.data),
                identifier = options.camelCase ? Json[options.identifier].replace(
                    /-([A-Za-z0-9])/gi,
                    (s, group1) => group1.toUpperCase()
                ) : Json[options.identifier],
                Events     = Handlers[identifier];

            if (Events) {
                Events.forEach(
                    (Event) => {
                        //Event.callback.apply(Event.thisArg, [Json.data])
                        //Adapt to all respone format
                        Event.callback.apply(Event.thisArg, [Json])
                    }
                )
            }
        }

        /**
         * {EventListener} For When the Websocket Client Closes the Connection
         *
         * @param {CloseEvent} close
         */
        function closeHandler(close) {
            if (options.closeHandler) options.closeHandler(close);

            if (pumpInterval) {
                window.clearInterval(pumpInterval);
                pumpInterval = null
            }

            Client = null
        }

        /**
         * Attaches Handlers to the Event Pump System
         *
         * @param {Boolean} server      True/False whether the Server should process the trigger
         * @param {String} identifier   Unique Name of the trigger
         * @param {Function} callback   Function to be called when the trigger is tripped
         * @param {Object} [thisArg]    Arguement to be passed to the handler as `this`
         */
        function attachHandler(identifier, callback, thisArg) {
            identifier = options.camelCase ? identifier.replace(
                /-([A-Za-z0-9])/gi,
                (s, group1) => group1.toUpperCase()
            ) : identifier;

            !(Handlers[identifier] || (Handlers[identifier] = [])).push({
                callback: callback,
                thisArg: thisArg
            })
        }

        /**
         * Detaches Handlers from the Event Pump System
         *
         * @param {String} identifier   Unique Name of the trigger
         * @param {Function} callback   Function to be called when the trigger is tripped
         */
        function detachHandler(identifier, callback) {
            if (arguments.length === 0) {
                Handlers = Object.create(null);
                return
            }

            var Handler = Handlers[identifier];
            if (!Handler) return;

            if (arguments.length === 1) {
                Handlers[identifier] = null;
                return
            }

            for (var index = Handler.length - 1; index >= 0; index--) {
                if (Handler[index].callback === callback || Handler[index].callback.fn === callback) {
                    Handler.splice(index, 1)
                }
            }
        }


        /**
         * Handles Event Triggers
         *
         * @param {String} identifier
         * @returns
         */
        function emitUrlHandler(identifier) {
            var url  = arguments[1] || '/';
            var args = arguments[2] || [];
            if (arguments.length > 3) {
                args = arguments.length > 1 ? [].slice.apply(arguments, [1]) : []
            }

            if (typeof args === "object") {
                args.identifier = identifier;

                socketPump.push(JSON.stringify(args));
                return
            }

            socketPump.push(
                JSON.stringify({
                    "event": identifier,
                    "url": url,
                    'arguments': args
                })
            )
        }

        function emitHandler(identifier) {
            console.log(arguments);
            var args = arguments[1] || [];
            if (arguments.length > 2) {
                args = arguments.length > 1 ? [].slice.apply(arguments, [1]) : []
            }

            if (typeof args === "object") {
                args.identifier = identifier;

                socketPump.push(JSON.stringify(args));
                return
            }

            socketPump.push(
                JSON.stringify({
                    "event": identifier,
                    "url": identifier,
                    'arguments': args
                })
            )
        }

        /**
         * Sends Messages to the Websocket Server every 250 ms
         *
         * @returns
         */
        function pumpHandler() {
            if (socketPump.length === 0) return;
            if (!Client) connect();

            if (Client.readyState === WebSocket.OPEN) {
                socketPump.forEach(
                    (item) => Client.send(item)
                );

                socketPump.length = 0
            }
        }

        if (!pumpInterval) window.setInterval(pumpHandler, 250);

        return {
            connect: connect,
            disconnect: function () {
                if (Client) {
                    Client.close();
                    Client = null
                }
            },
            attach: attachHandler,
            detach: detachHandler,
            emitUrl: emitUrlHandler,
            emit: emitHandler,
            on: function (identifier, callback) {
                this.attach(identifier, callback, this);
                return this
            },
            once: function (identifier, callback) {
                const thisArg = this;

                function once() {
                    this.detach(identifier, callback);
                    callback.apply(thisArg, arguments)
                }

                once.fn = callback;

                this.attach(identifier, once, thisArg);
                return thisArg
            },
            off: function (identifier, callback) {
                this.detach(identifier, callback, this);
                return this
            }
        }
    };

    return WsClient;
});