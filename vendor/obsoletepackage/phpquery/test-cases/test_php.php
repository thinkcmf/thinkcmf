<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
<?php
require_once('../phpQuery/phpQuery.php');
phpQuery::$debug = true;

$testName = 'PHP Code output';
$expected = <<<EOF
<?php  print \$r  ?><a href="<?php print \$array['key']; if ("abc'd'") {}; ?>"></a>
EOF;
$result = phpQuery::newDocumentPHP(null, 'text/html;charset=utf-8')
	->appendPHP('print $r')
	->append('<a/>')
		->find('a')
			->attrPHP('href', 'print $array[\'key\']; if ("abc\'d\'") {};')
		->end();
if (trim($result->php()) == $expected)
	print "Test '{$testName}' passed :)";
else
	print "Test '{$testName}' <strong>FAILED</strong> !!!";
print "\n";

$testName = 'PHP file open';
$result = phpQuery::newDocumentFilePHP('document-types/document-utf8.php');
var_dump($result->php());
/*
	->appendPHP('print $r')
	->append('<a/>')
		->find('a')
			->attrPHP('href', 'print $array[\'key\']; if ("abc\'d\'") {};')
		->end();
if (trim($result->php()) == $expected)
	print "Test '{$testName}' passed :)";
else
	print "Test '{$testName}' <strong>FAILED</strong> !!!";
print "\n";
*/