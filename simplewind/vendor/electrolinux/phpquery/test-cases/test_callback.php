<?php
//if (PHP_VERSION < 5.3)
//	throw new Exception("This test case is only for PHP 5.3 and above.");
require('/home/bob/Sources/php/simpletest/simpletest/trunk/autorun.php');
require_once('../phpQuery/phpQuery.php');
phpQuery::$debug = true;

class CallbackTest extends UnitTestCase {
	public function callback2() {
		return 'callback2';
	}
	public function callback1($self) {
		return $self;
	}	
	public function testExtend() {
		$newMethods = array(
			'newMethod1' => array($this, 'callback1'),
			'newMethod2' => array($this, 'callback2'),
		);
		phpQuery::extend('phpQueryObject', $newMethods);
		$doc = phpQuery::newDocumentXML("<div/>");
		$this->assertTrue($doc->newMethod1() == $doc,
			'$doc->newMethod1 == $doc');
		$this->assertTrue($doc->newMethod2() == "callback2",
			'$doc->newMethod1 == "callback2"');  
	}
}