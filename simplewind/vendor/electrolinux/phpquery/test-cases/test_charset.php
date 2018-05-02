<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<?php
require_once('../phpQuery/phpQuery.php');
// phpQuery::$debug = true;

$testName = 'Text node append';
$result = phpQuery::newDocumentFile('test.html')
	->find('li:first')
		->find('p:first')
			->html('żźć');
if (trim($result->html()) == 'żźć')
	print "Test '{$testName}' passed :)<br />\n";
else
	print "Test '{$testName}' <strong>FAILED</strong> !!!<br />\n";
print "\n";

$testName = 'Text node HTML entite append';
$result = phpQuery::newDocumentFile('test.html')
	->find('li:first')
		->find('p:first')
			->_empty()
			->append('&eacute;');
if (trim($result->html()) == 'é')
	print "Test '{$testName}' passed :)<br />\n";
else {
	print "Test '{$testName}' <strong>FAILED</strong> !!!<br />\n";
	print $result->html();
}
print "\n";

$testName = 'DOMElement node HTML entite append';
$result = phpQuery::newDocumentFile('test.html')
	->find('li:first')
		->find('p:first')
			->empty()
			->append('<span>&eacute;</span>');
if (trim($result->html()) == '<span>é</span>')
	print "Test '{$testName}' passed :)<br />\n";
else {
	print "Test '{$testName}' <strong>FAILED</strong> !!!<br />\n";
	print $result->html();
}
print "\n";

$testName = 'Append and move';
$result = phpQuery::newDocumentFile('test.html');
$li = $result->find('li:first');
$result->find('div')->_empty();
$li->html('test1-&eacute;-test1')
	->append('test2-é-test2')
	->appendTo(
		$result->find('div:first')
	);
$result = $result->find('div:first li:first');
$expected = 'test1-é-test1test2-é-test2';
if (trim(str_replace("\n", '', $result->html())) == $expected)
	print "Test '{$testName}' passed :)<br />\n";
else {
	print "Test '{$testName}' <strong>FAILED</strong> !!!<br />\n";
	print "'".trim($result->html())."'";
}
print "\n";

$testName = 'Attr charset';
$result = phpQuery::newDocumentFile('test.html')
	->find('li:first')
		->attr('test', 'foo &eacute; żźć bar');
if (trim($result->attr('test')) == 'foo &eacute; żźć bar')
	print "Test '{$testName}' passed :)<br />\n";
else {
	print "Test '{$testName}' <strong>FAILED</strong> !!!<br />\n";
	print $result->attr('test');
}
print "\n";


//$testName = 'Loading document without meta charset';
//$result = phpQuery::newDocumentFile('test.html')
//	->_empty();
////var_dump((string)$result->htmlOuter());
//$result = phpQuery::newDocument($result->htmlOuter());
//$validResult = <<<EOF
//<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
//<html xmlns="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html;charset=UTF-8" /></head></html>
//EOF;
//$similarity = 0;
//similar_text($result->htmlOuter(), $validResult, $similarity);
//if ( $similarity > 90 )
//	print "Test '{$testName}' passed :)<br />\n";
//else
//	print "Test '{$testName}' <strong>FAILED</strong> !!! ";
//print "<pre>";
//print $result;
//print "</pre>\n";