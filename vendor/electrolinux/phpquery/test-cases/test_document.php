<?php
//error_reporting(E_ALL);
require_once('../phpQuery/phpQuery.php');
phpQuery::$debug = true;


$testName = 'HTML document load';
$doc = phpQuery::newDocumentFile('test.html');
print $doc->find('li:first')->html('foo <p>bar</p> foo <b><i>foo</i</b>')->html();
die();
$testResult = 10;
if ($doc->script('example', 'p')->length == $testResult)
	print "Test '$testName' PASSED :)";
else {
	print "Test '$testName' <strong>FAILED</strong> !!! ";
	print "<pre>";
	var_dump($doc->whois());
	print "</pre>\n";
}
print "\n";