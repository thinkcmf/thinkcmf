<?php
require_once('../phpQuery/phpQuery.php');
// phpQuery::$debug = true;
$form = <<<EOF
<form>
  <input name='input-example'>
  <input name='array[array-example]'>
  <textarea name='textarea-example'></textarea>
	<select name='select-example'>
    <option value='first'></option>
	</select>
  <input type='radio' name='radio-example' value='foo'>
  <input type='checkbox' name='checkbox-example' value='foo'>
</form>
EOF;
$doc = phpQuery::newDocumentHTML($form);
$inputs = $doc['form > *'];
// creates array from input names
// $results = $inputs->get(null,
// 	create_function('$node', 'return $node->getAttribute("name");')
// );
$results = array();
foreach($inputs as $node) {
	$node = pq($node);
	$name = $node->attr('name');
	$results[$name] = false;
	$node->change(
		new CallbackReference($results[$name])
	);
}
$inputs
	->not('select,:checkbox,:radio')
		->val('new value')
	->end()
	->filter('select')
		->val('first')
	->end()
	->filter(':checkbox')
		->val(array('foo'))
	->end()
	->filter(':radio')
		->val(array('foo'))
	->end()
;
foreach($results as $name => $result) {
	print $result
		? "Test for '$name' PASSED :)<br />\n"
		: "Test for '$name' <strong>FAILED</strong> !!!<br />\n";
}