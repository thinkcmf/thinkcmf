<?php
require_once('../phpQuery/phpQuery.php');
phpQuery::$debug = true;
$testName = 'Selectors';
$tests = array(
	array(
		'div:first',
		array(
			'div.articles',
		)
	),
	array(
		"p:contains('title')",
		array(
			'p.title',
			'p.title',
			'p.noTitle',
		)
	),
	array(
		"p:contains('title 2')",
		array(
			'p.title',
		)
	),
	array(
		'li:eq(1)',
		array(
			'li#testID',
		)
	),
	array(
		'li:eq(1) p:eq(1)',
		array(
			'p.title',
		)
	),
	array(
		'*[rel="test"]',
		array(
			'p',
			'p'
		)
	),
	array(
		'#testID p:first',
		array(
			'p'
		)
	),
	array(
		"p:not('.title'):not('.body')",
		array(
			'p',
			'p',
			'p',
			'p.noTitle',
			'p.after',
		)
	),
	array(
		"[content*=html]",
		array(
			'meta'
		)
	),
	array(
		"li#testID, div.articles",
		array(
			'li#testID',
			'div.articles'
		)
	),
	array(
		"script[src]:not([src^=<?php])",
		array(
			'script'
		)
	),
//	array(
//		'li:not([ul/li])',
//		array(
//			'li',
//			'li#testID',
//			'li',
//			'li.nested',
//			'li.second',
//		)
//	),
	array(
		'li:has(ul)',
		array(
			'li#i_have_nested_list',
		)
	),
	array(
		'p[rel] + p',
		array(
			'p.title',
			'p.noTitle',
		)
	),
	array(
		'ul:first > li:first ~ *',
		array(
			'li#testID',
			'li',
		)
	),
	// CSS3 pseudoclasses
	array(
		'li:only-child',
		array(
			'li.nested',
		)
	),
	array(
		'p[rel=test]:parent',
		array(
			'p',
			'p',
		)
	),
	array(
		'li:first-child',
		array(
			'li',
			'li#i_have_nested_list',
			'li.nested',
		)
	),
	array(
		':last-child',
		array(
			'html',
			'script',
			'body',
			'p.body',
			'p.body',
			'li',
			'p.body',
			'p.after',
			'ul',
			'ul',
			'li.nested',
			'li.second',
		)
	),
	array(
		':nth-child(1n+1)',
		array(
			'html',
			'head',
			'meta',
			'div.articles',
			'ul',
			'li',
			'p',
			'p',
			'p',
			'li#i_have_nested_list',
			'ul',
			'li.nested',
		)
	),
	array(
		':nth-child(3n+6)',
		array(
			'script',
			'p.body',
			'p.body',
			'li',
			'p.body',
		)
	),
	array(
		':nth-child(2n)',
		array(
			'title',
			'script',
			'body',
			'p.title',
			'li#testID',
			'p.title',
			'p.noTitle',
			'p.after',
			'ul',
			'li.second',
		)
	),
	array(
		':nth-child(1)',
		array(
			'html',
			'head',
			'meta',
			'div.articles',
			'ul',
			'li',
			'p',
			'p',
			'p',
			'li#i_have_nested_list',
			'ul',
			'li.nested',
		)
	),
	array(
		':nth-child(odd)',
		array(
			'html',
			'head',
			'meta',
			'script',
			'div.articles',
			'ul',
			'li',
			'p',
			'p.body',
			'p',
			'p.body',
			'li',
			'p',
			'p.body',
			'li#i_have_nested_list',
			'ul',
			'li.nested',
		)
	),
	array(
		':nth-child(even)',
		array(
			'title',
			'script',
			'body',
			'p.title',
			'li#testID',
			'p.title',
			'p.noTitle',
			'p.after',
			'ul',
			'li.second',
		)
	),
	array(
		':empty',
		array(
			'meta',
			'script',
			'script',
			'li.nested',
		)
	),


//	array(
//		'',
//		array(
//			'',
//		)
//	),
//	array(
//		'',
//		array(
//			'',
//		)
//	),
//	array(
//		'',
//		array(
//			'',
//		)
//	),
);

phpQuery::newDocumentFile('test.html');
foreach( $tests as $k => $test ) {
	$tests[ $k ][2] = pq( $test[0] )->whois();
}
foreach( $tests as $test ) {
	if ( $test[1] == $test[2] )
		print "Test '{$test[0]}' PASSED :)";
	else {
		print "Test '{$test[0]}' <strong>FAILED</strong> !!!";
		print_r($test[2]);
	}
	print "<br /><br />";
}

//
$testName = 'Complicated selector 1';
phpQuery::newDocumentFile('test.html');
pq('<select name="test[]"><option value=3>test</option></select>')
	->appendTo('body');
$result = pq('select[name="test[]"]:has(option[value=3])');
if ( $result->size() == 1 )
	print "Test '{$testName}' PASSED :)";
else
	print "Test '{$testName}' <strong>FAILED</strong> !!! ";
$result->dump();
print "\n";
?>