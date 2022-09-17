<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>HTML document with UTF-8 charset</title>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
		<?php print '<title>foob\'"bar</title>'; ?>
	</head>
	<body>
		<span>Hello World!</span>
		<span>ąśżźć</span>
		<a href='<?php foreach($foo as $bar) { print $foo['1'] } ?>'>Attr test</a>
	</body>
</html>
