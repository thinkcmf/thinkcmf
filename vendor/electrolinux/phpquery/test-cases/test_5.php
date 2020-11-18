<?php
require_once('../phpQuery/phpQuery.php');
phpQuery::$debug = true;

// CALLBACKS
class callbackClass {
	static function staticMethodCallback($node) {
		pq($node)->addClass('newClass');
	}
	function methodCallback($node) {
		pq($node)->addClass('newClass');
	}
}
function functionCallback($node) {
	pq($node)->addClass('newClass');
}
$testResult = array(
	'li.newClass',
	'li#testID.newClass',
	'li.newClass',
	'li#i_have_nested_list.newClass',
	'li.nested.newClass',
	'li.second.newClass',
);
$tests = array(
	'functionCallback',
	array('callbackClass', 'staticMethodCallback'),
	array(new callbackClass, 'methodCallback')
);
foreach($tests as $test) {
	$result = phpQuery::newDocumentFile('test.html')
		->find('li')
			->each($test);
	$testName = is_array($test)
		? $test[1]
		: $test;
	if ( $result->whois() == $testResult )
		print "Test '$testName' PASSED :)";
	else {
		print "Test '$testName' <strong>FAILED</strong> !!! ";
		print "<pre>";
		print_r($result->whois());
		print "</pre>\n";
	}
	print "\n";
}
?>