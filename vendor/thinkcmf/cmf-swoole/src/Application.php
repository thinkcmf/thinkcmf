<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think\swoole;

use Swoole\Http\Request;
use Swoole\Http\Response;
use think\App;
use think\Error;
use think\exception\HttpException;

/**
 * Swoole应用对象
 */
class Application extends App
{
    /**
     * 处理Swoole请求
     * @access public
     * @param  \Swoole\Http\Request $request
     * @param  \Swoole\Http\Response $response
     * @param  void
     */
    public function swoole(Request $request, Response $response)
    {
        try {
            ob_start();

            // 重置应用的开始时间和内存占用
            $this->beginTime = microtime(true);
            $this->beginMem  = memory_get_usage();

            // 销毁当前请求对象实例
            $this->delete('think\Request');

            // 设置Cookie类Response
            $this->cookie->setResponse($response);

            $_COOKIE = $request->cookie ?: [];
            $_GET    = $request->get ?: [];
            $_POST   = $request->post ?: [];
            $_FILES  = $request->files ?: [];
            $header  = $request->header ?: [];
            $server  = array_change_key_case($request->server, CASE_UPPER);
            $_SERVER = array_merge($server, array_change_key_case($header, CASE_UPPER));

            // 重新实例化请求对象 处理swoole请求数据
            $this->request->withHeader($header)
                ->withServer($_SERVER)
                ->withGet($_GET)
                ->withPost($_POST)
                ->withCookie($_COOKIE)
                ->withInput($request->rawContent())
                ->withFiles($_FILES)
                ->setBaseUrl($request->server['request_uri'])
                ->setUrl($request->server['request_uri'] . (!empty($request->server['query_string']) ? '&' . $request->server['query_string'] : ''))
                ->setHost($request->header['host'])
                ->setPathinfo(ltrim($request->server['path_info'], '/'));

            // 更新请求对象实例
            $this->route->setRequest($this->request);

            // 重新加载全局中间件
            if (is_file($this->appPath . 'middleware.php')) {
                $middleware = include $this->appPath . 'middleware.php';
                if (is_array($middleware)) {
                    $this->middleware->import($middleware);
                }
            }

            $resp = $this->run();
            $resp->send();

            $content = ob_get_clean();
            $status  = $resp->getCode();

            // Trace调试注入
            if ($this->env->get('app_trace', $this->config->get('app_trace'))) {
                $this->debug->inject($resp, $content);
            }

            // 清除中间件数据
            $this->middleware->clear();

            // 发送状态码
            $response->status($status);

            // 发送Header
            foreach ($resp->getHeader() as $key => $val) {
                $response->header($key, $val);
            }

            $response->end($content);
        } catch (HttpException $e) {
            $this->exception($response, $e);
        } catch (\Exception $e) {
            $this->exception($response, $e);
        } catch (\Throwable $e) {
            $this->exception($response, $e);
        }
    }

    public function swooleWebSocket($server, $frame)
    {
        try {
            // 重置应用的开始时间和内存占用
            $this->beginTime = microtime(true);
            $this->beginMem  = memory_get_usage();

            // 销毁当前请求对象实例
            $this->delete('think\Request');
            WebSocketFrame::destroy();
            $request = $frame->data;
            $request = json_decode($request, true);
            // 重置应用的开始时间和内存占用
            $this->beginTime = microtime(true);
            $this->beginMem  = memory_get_usage();
            WebSocketFrame::getInstance($server, $frame);
            $_COOKIE                    = isset($request['arguments']['cookie']) ? $request['arguments']['cookie'] : [];
            $_GET                       = isset($request['arguments']['get']) ? $request['arguments']['get'] : [];
            $_POST                      = isset($request['arguments']['post']) ? $request['arguments']['post'] : [];
            $_FILES                     = isset($request['arguments']['files']) ? $request['arguments']['files'] : [];
            $_SERVER["PATH_INFO"]       = $request['url'] ?: '/';
            $_SERVER["REQUEST_URI"]     = $request['url'] ?: '/';
            $_SERVER["SERVER_PROTOCOL"] = 'http';
            $_SERVER["REQUEST_METHOD"]  = 'post';

            // 重新实例化请求对象 处理swoole请求数据
            $this->request
                ->withServer($_SERVER)
                ->withGet($_GET)
                ->withPost($_POST)
                ->withCookie($_COOKIE)
                ->withInput($request->rawContent())
                ->withFiles($_FILES)
                ->setBaseUrl($request->server['request_uri'])
                ->setUrl($request->server['request_uri'] . (!empty($request->server['query_string']) ? '&' . $request->server['query_string'] : ''))
                ->setHost($request->header['host'])
                ->setPathinfo(ltrim($request->server['path_info'], '/'));

            // 更新请求对象实例
            $this->route->setRequest($this->request);

            $resp = $this->run();
            $resp->send();

        } catch (HttpException $e) {
            $this->webSocketException($server, $frame, $e);
        } catch (\Exception $e) {
            $this->webSocketException($server, $frame, $e);
        } catch (\Throwable $e) {
            $this->webSocketException($server, $frame, $e);
        }
    }

    protected function exception($response, $e)
    {
        if ($e instanceof \Exception) {
            $handler = Error::getExceptionHandler();
            $handler->report($e);

            $resp    = $handler->render($e);
            $content = $resp->getContent();
            $code    = $resp->getCode();

            $response->status($code);
            $response->end($content);
        } else {
            $response->status(500);
            $response->end($e->getMessage());
        }

        throw $e;
    }

    protected function webSocketException($server, $frame, $e)
    {
        $response = [
            'code'    => $e->code,
            'content' => $e->getMessage(),
        ];
        $server->push($frame->fd, json_encode($response));

        throw $e;
    }
}
