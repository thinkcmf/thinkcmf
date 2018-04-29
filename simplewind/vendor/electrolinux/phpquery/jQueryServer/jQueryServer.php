`<?php
/**
 * jQuery Server Plugin
 *
 * Backend class using phpQuery.
 *
 * @version 0.5.1
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 * @link http://code.google.com/p/phpquery/wiki/jQueryServer
 * @link http://code.google.com/p/phpquery/
 * @todo local files support (safe...)
 * @todo respond with proper HTTP code
 * @todo persistant thread support (with timeout...)
 * @todo 2.0: JSON RPC - Zend_Json_Server
 * @todo 2.0: XML RPC ?
 */
class jQueryServer {
	public $config = array(
		'allowedRefererHosts' => array('.'),
		'refererMustMatch' => true,
	);
	public $calls = null;
	public $options = null;
	public $allowedHosts = null;
	function __construct($data) {
		$pq = null;
		include_once(dirname(__FILE__).'/../phpQuery/phpQuery.php');
		if (file_exists(dirname(__FILE__).'/jQueryServer.config.php')) {
			include_once(dirname(__FILE__).'/jQueryServer.config.php');
			if ($jQueryServerConfig)
				$this->config = array_merge_recursive($this->config, $jQueryServerConfig);
		}
		if ($this->config['refererMustMatch']) {
			foreach($this->config['allowedRefererHosts'] as $i => $host)
				if ($host == '.')
					$this->config['allowedRefererHosts'][$i] = $_SERVER['HTTP_HOST'];
			$referer = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
			$authorized = $referer
				&& in_array($referer, $this->config['allowedRefererHosts']);
			if (! $authorized) {
				throw new Exception("Host '{$_SERVER['HTTP_REFERER']}' not authorized to make requests.");
				return;
			}
		}
//		phpQueryClass::$debug = true;
//		if (! function_exists('json_decode')) {
//			include_once(dirname(__FILE__).'/JSON.php');
//			$this->json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
//		}
//		$data = $this->jsonDecode($data);
		$data = phpQuery::parseJSON($data);
		// load document (required for first $data element)
		if (is_array($data[0]) && isset($data[0]['url'])) {
			$this->options = $data[0];
			$ajax = $this->options;
			$this->calls = array_slice($data, 1);
			$ajax['success'] = array($this, 'success');
			phpQuery::ajax($ajax);
		} else {
			throw new Exception("URL needed to download content");
		}
	}
	public function success($response) {
		$pq = phpQuery::newDocument($response);
		foreach($this->calls as $k => $r) {
			// check if method exists
			if (! method_exists(get_class($pq), $r['method'])) {
				throw new Exception("Method '{$r['method']}' not implemented in phpQuery, sorry...");
			// execute method
			} else {
				$pq = call_user_func_array(
					array($pq, $r['method']),
					$r['arguments']
				);
			}
		}
		if (! isset($this->options['dataType']))
			$this->options['dataType'] = '';
		switch(strtolower($this->options['dataType'])) {
			case 'json':
				if ( $pq instanceof PHPQUERYOBJECT ) {
					$results = array();
					foreach($pq as $node)
						$results[] = pq($node)->htmlOuter();
					print phpQuery::toJSON($results);
				} else {
					print phpQuery::toJSON($pq);
				}
			break;
			default:
				print $pq;
		}
		// output results
	}
//	public function jsonEncode($data) {
//		return function_exists('json_encode')
//			? json_encode($data)
//			: $this->json->encode($data);
//	}
//	public function jsonDecode($data) {
//		return function_exists('json_decode')
//			? json_decode($data, true)
//			: $this->json->decode($data);
//	}
}
new jQueryServer($_POST['data']);
?>