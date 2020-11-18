<?php
require_once('../phpQuery/phpQuery.php');
phpQuery::$debug = true;

// SLICE1
$testResult = array(
	'li#testID',
);
$result = phpQuery::newDocumentFile('test.html')
	->find('li')
		->slice(1, 2);
if ( $result->whois() == $testResult )
	print "Test 'Slice1' PASSED :)";
else {
	print "Test 'Slice1' <strong>FAILED</strong> !!! ";
	print "<pre>";
	print_r($result->whois());
	print "</pre>\n";
}
print "\n";

// SLICE2
$testResult = array(
	'li#testID',
	'li',
	'li#i_have_nested_list',
	'li.nested',
);
$result = phpQuery::newDocumentFile('test.html')
	->find('li')
		->slice(1, -1);
if ( $result->whois() == $testResult )
	print "Test 'Slice2' PASSED :)";
else {
	print "Test 'Slice2' <strong>FAILED</strong> !!! ";
	print "<pre>";
	print_r($result->whois());
	print "</pre>\n";
}
print "\n";

// Multi-insert
$result = phpQuery::newDocument('<li><span class="field1"></span><span class="field1"></span></li>')
	->find('.field1')
		->php('longlongtest');
$validResult = '<li><span class="field1"><php>longlongtest</php></span><span class="field1"><php>longlongtest</php></span></li>';
similar_text($result->htmlOuter(), $validResult, $similarity);
if ( $similarity > 80 )
	print "Test 'Multi-insert' PASSED :)";
else {
	print "Test 'Multi-insert' <strong>FAILED</strong> !!! ";
	print "<pre>";
	var_dump($result->htmlOuter());
	print "</pre>\n";
}
print "\n";

// INDEX
$testResult = 1;
$result = phpQuery::newDocumentFile('test.html')
	->find('p')
		->index(pq('p.title:first'));
if ( $result == $testResult )
	print "Test 'Index' PASSED :)";
else {
	print "Test 'Index' <strong>FAILED</strong> !!! ";
}
print "\n";

// CLONE
$testName = 'Clone';
$testResult = 3;
$document;
$p = phpQuery::newDocumentFile('test.html')
	->toReference($document)
	->find('p:first');
foreach(array(0,1,2) as $i) {
	$p->clone()
		->addClass("clone-test")
		->addClass("class-$i")
		->insertBefore($p);
}
if (pq('.clone-test')->size() == $testResult)
	print "Test '$testName' PASSED :)";
else {
	print "Test '$testName' <strong>FAILED</strong> !!! ";
}
print "\n";

// SIBLINGS
$testName = 'Next';
$testResult = 3;
$document;
$result = phpQuery::newDocumentFile('test.html')
	->find('li:first')
	->next()
	->next()
	->prev()
	->is('#testID');
if ($result)
	print "Test '$testName' PASSED :)";
else {
	print "Test '$testName' <strong>FAILED</strong> !!! ";
}
print "\n";
?>


<?php die();