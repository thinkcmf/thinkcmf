<?php
require_once('../phpQuery/phpQuery.php');
phpQuery::$debug = true;

$testName = 'Attribute change';
$expected = 'new attr value';
$result = phpQuery::newDocumentFile('test.html')
	->find('p[rel]:first')
		->attr('rel', $expected);
if ($result->attr('rel') == $expected)
	print "Test '{$testName}' passed :)";
else
	print "Test '{$testName}' <strong>FAILED</strong> !!!";
print "\n";


$testName = 'Attribute change in iteration';
$expected = 'new attr value';
$doc = phpQuery::newDocumentFile('test.html');
foreach($doc['p[rel]:first'] as $p)
	pq($p)->attr('rel', $expected);
if ($doc['p[rel]:first']->attr('rel') == $expected)
	print "Test '{$testName}' passed :)";
else
	print "Test '{$testName}' <strong>FAILED</strong> !!!";
print "\n";