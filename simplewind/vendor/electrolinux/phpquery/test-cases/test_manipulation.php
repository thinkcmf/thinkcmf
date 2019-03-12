<?php
require_once('../phpQuery/phpQuery.php');
phpQuery::$debug = true;


$testName = 'Simple data insertion';
$testResult = <<<EOF
<div class="articles">
			div.articles text node
            <ul>

            <li>
                	<p>This is paragraph of first LI</p>
                    <p class="title">News 1 title</p>
                    <p class="body">News 1 body</p>
                </li>

<li>
                	<p>This is paragraph of first LI</p>
                    <p class="title">News 2 title</p>
                    <p class="body">News 2 body</p>
                </li>
<li>
                	<p>This is paragraph of first LI</p>
                    <p class="title">News 3</p>
                    <p class="body">News 3 body</p>
                </li>
</ul>
<p>paragraph after UL</p>
        </div>
EOF;
$rows = array(
	array(
		'title' => 'News 1 title',
		'body'	=> 'News 1 body',
	),
	array(
		'title' => 'News 2 title',
		'body'	=> 'News 2 body',
	),
	array(
		'title' => 'News 3',
		'body'	=> 'News 3 body',
	),
);
phpQuery::newDocumentFile('test.html');
$articles = pq('.articles ul');
$rowSrc = $articles->find('li')
	->remove()
	->eq(0);
foreach( $rows as $r ) {
	$row = $rowSrc->_clone();
	foreach( $r as $field => $value ) {
		$row->find(".{$field}")
			->html($value);
//		die($row->htmlOuter());
	}
	$row->appendTo($articles);
}
$result = pq('.articles')->htmlOuter();
//print htmlspecialchars("<pre>{$result}</pre>").'<br />';
$similarity = 0.0;
similar_text($testResult, $result, $similarity);
if ($similarity > 90)
	print "Test '{$testName}' passed :)";
else
	print "Test '{$testName}' <strong>FAILED</strong> ($similarity) !!!";
print "\n";


$testName = 'Parent && children';
$result = phpQuery::newDocumentFile('test.html');
$parent = $result->find('ul:first');
$children = $parent->find('li:first');
$e = null;
try {
	$children->before('<li>test</li>');
} catch(Exception $e) {
	print "Test '{$testName}' <strong>FAILED</strong> !!! ";
}
if (! $e) {
	print "Test '{$testName}' PASSED :)";
}
print "\n";


$testName = 'HTML insertion';
$doc = phpQuery::newDocument('<div><p/></div>');
$string = "La Thermo-sonde de cuisson vous permet de cuire à la perfection au four comme au bain-marie. Température: entre <b>0°C et 210°C</b>.";
$doc->find('p')->html($string);
if (pq('p')->length == 1)
	print "Test '{$testName}' PASSED :)";
else {
	print "Test '{$testName}' <strong>FAILED</strong> !!! ";
	print $doc->htmlOuter('htmlentities');
}
print "\n";


$testName = 'HTML insertion 2';
$doc = phpQuery::newDocument('<div><p/></div>');
$string = "<div>La Thermo-sonde de cuisson vous permet de cuire à la perfection au four comme au bain-marie. Température: entre <b>0°C et 210°C</b>.</div>";
$doc->find('p')->html($string);
if (pq('div')->length == 2) {
	print "Test '{$testName}' PASSED :)";
} else {
	print "Test '{$testName}' <strong>FAILED</strong> !!! ";
	print $doc->htmlOuter('htmlentities');
}
print "\n";


$testName = 'HTML insertion 3';
$doc = phpQuery::newDocument('<div><p/></div>');
$string = 'Hors paragraphe.
<img align="right" src="http://www.stlouisstpierre.com/institution/images/plan.jpg">
<p>Éditorial de l\'institution Saint-Pierre.</p>
 Hors paragraphe.';
$doc->find('p')->html($string);
if (pq('img')->length == 1) {
	print "Test '{$testName}' PASSED :)";
	print $doc->htmlOuter();
} else {
	print "Test '{$testName}' <strong>FAILED</strong> !!! ";
	print $doc->htmlOuter('htmlentities');
}
print "\n";




$testName = 'Text insertion';
$doc = phpQuery::newDocument('<div><p/></div>');
$string = "La Thermo-sonde de cuisson vous permet de cuire à la perfection au four comme au bain-marie";
$doc->find('p')->html($string);
if (trim(pq('p:first')->html()) == $string)
	print "Test '{$testName}' PASSED :)";
else {
	print "Test '{$testName}' <strong>FAILED</strong> !!! ";
	print $doc->htmlOuter('htmlentities');
}
print "\n";
?>