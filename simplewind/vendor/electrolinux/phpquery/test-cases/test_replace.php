<?php
require_once('../phpQuery/phpQuery.php');
phpQuery::$debug = true;

$testName = 'ReplaceWith';
phpQuery::newDocumentFile('test.html')
	->find('p:eq(1)')
		->replaceWith("<p class='newTitle'>
                        this is example title
                    </p>");
$result = pq('p:eq(1)');
if ( $result->hasClass('newTitle') )
	print "Test '{$testName}' PASSED :)";
else
	print "Test '{$testName}' <strong>FAILED</strong> !!! ";
$result->dump();
print "\n";



$testName = 'ReplaceAll';
$testResult = 3;
phpQuery::newDocumentFile('test.html');
pq('<div class="replacer">')
	->replaceAll('li:first p');
$result = pq('.replacer');
if ( $result->size() == $testResult )
	print "Test '{$testName}' PASSED :)";
else
	print "Test '{$testName}' <strong>FAILED</strong> !!! ";
$result->dump();
print "\n";