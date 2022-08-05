<?php
require_once('../phpQuery/phpQuery.php');
phpQuery::$debug = true;

$testName = 'Multi document append phpQuery object';
$testResult = array(
	'p.body',
);
$doc1 = phpQuery::newDocumentFile('test.html');
$doc2 = phpQuery::newDocumentFile('test.html');

foreach ($doc1->find('p') as $node)
   $doc2->find('body')->append(pq($node));
$testResult = $doc2->find('p');
if ( $testResult->size() == 2*$doc1->find('p')->size() )
	print "Test '{$testName}' PASSED :)";
else {
	print "Test '{$testName}' <strong>FAILED</strong> !!!<br />";
	$testResult->whois();
}

$testName = 'Multi document append DOMNode';
$testResult = array(
	'p.body',
);
$doc1 = phpQuery::newDocumentFile('test.html');
$doc2 = phpQuery::newDocumentFile('test.html');
foreach ($doc1->find('p') as $node)
   $doc2->find('body')->append($node);
$testResult = $doc2->find('p');
if ( $testResult->size() == 2*$doc1->find('p')->size() )
	print "Test '{$testName}' PASSED :)";
else {
	print "Test '{$testName}' <strong>FAILED</strong> !!!<br />";
	$testResult->whois();
}
?>
