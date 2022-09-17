<?php
//error_reporting(E_ALL);
set_include_path(
	get_include_path()
	.':/home/bob/Sources/PHP/zend-framework/'
);

require_once('../phpQuery/phpQuery.php');
phpQuery::$debug = true;
$testHtml = phpQuery::newDocumentFile('test.html');
$testHtml['li:first']->append('<span class="just-added">test</span>');
$testName = 'Array Access get';
if (trim($testHtml['.just-added']->html()) == 'test')
	print "Test '$testName' PASSED :)";
else {
	print "Test '$testName' <strong>FAILED</strong> !!! ";
	print "<pre>";
	print_r($testHtml['.just-added']->whois());
	print "</pre>\n";
}
print "\n";

require_once('../phpQuery/phpQuery.php');
phpQuery::$debug = true;
$testHtml = phpQuery::newDocumentFile('test.html');
$testHtml['li:first'] = 'new inner html';
$testName = 'Array Access set';
if (trim($testHtml['li:first']->html()) == 'new inner html')
	print "Test '$testName' PASSED :)";
else {
	print "Test '$testName' <strong>FAILED</strong> !!! ";
	print "<pre>";
	print_r($testHtml['.just-added']->whois());
	print "</pre>\n";
}
print "\n";
