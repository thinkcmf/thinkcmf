<?php
/**
 * Charset and document types test.
 *
 * Remember:
 * - never test charset with htmlentities ! Use htmlspecialchars (or define charset as parameter)
 *
 * TODO:
 * - document fragments tests (with all 4 charset scenarios)
 *
 */
class phpQuery {
	static $defaultDocumentID;
	static $debug = 0;
	static $documents = array();
	static $defaultCharset = 'utf-8';
	static function debug($text) {
		if (self::$debug)
			print var_dump($text);
	}
}
require_once('../phpQuery/DOMDocumentWrapper.php');
phpQuery::$debug = 2;

/* ENCODINGS */
//print '<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-2">';
print '<meta http-equiv="Content-Type" content="text/html;charset=utf-8">';

/* HTML */

//$htmlIso = new DOMDocumentWrapper(
//	file_get_contents('document-types/document-iso88592.html')
//);
//$htmlIsoNoCharset = new DOMDocumentWrapper(
//	file_get_contents('document-types/document-iso88592-nocharset.html'),
//	'text/html;charset=iso-8859-2'
//);
$htmlUtf = new DOMDocumentWrapper(
	file_get_contents('document-types/document-utf8.html')
);
var_dump($htmlUtf->markup());
//$htmlUtfNoCharset = new DOMDocumentWrapper(
//	file_get_contents('document-types/document-utf8-nocharset.html'),
//	'text/html;charset=utf-8'
//);
//print htmlspecialchars($htmlIso->markup(
//	$htmlIso->document->getElementsByTagName('span'))
//);
//print htmlspecialchars($htmlIsoNoCharset->markup(
//	$htmlIsoNoCharset->document->getElementsByTagName('p'))
//);
//print htmlspecialchars($htmlUtf->markup());
//print htmlspecialchars($htmlUtfNoCharset->markup());

/* XML */

//$xmlIso = new DOMDocumentWrapper(
//	file_get_contents('document-types/document-iso88592.xml')
//);
//$xmlIsoNoCharset = new DOMDocumentWrapper(
//	file_get_contents('document-types/document-iso88592-nocharset.xml'),
//	'text/xml;charset=iso-8859-2'
//);
//$xmlUtf = new DOMDocumentWrapper(
//	file_get_contents('document-types/document-utf8.xml')
//);
//$xmlUtfNoCharset = new DOMDocumentWrapper(
//	file_get_contents('document-types/document-utf8-nocharset.xml'),
//	'text/xml;charset=utf-8'
//);
//print var_dump($xmlIso->markup(
//	$xmlIso->document->getElementsByTagName('step')->item(0)
//));
//print htmlspecialchars($xmlIsoNoCharset->markup());
//print htmlspecialchars($xmlUtf->markup());
//print htmlspecialchars($xmlUtfNoCharset->markup());

/* XHTML */

//$xhtmlIso = new DOMDocumentWrapper(
//	file_get_contents('document-types/document-iso88592.xhtml')
//);
//$xhtmlIsoNoCharset = new DOMDocumentWrapper(
//	file_get_contents('document-types/document-iso88592-nocharset.xhtml'),
//	'application/xhtml+xml;charset=iso-8859-2'
//);
//$xhtmlUtf = new DOMDocumentWrapper(
//	file_get_contents('document-types/document-utf8.xhtml')
//);
//$xhtmlUtfNoCharset = new DOMDocumentWrapper(
//	file_get_contents('document-types/document-utf8-nocharset.xhtml'),
//	'application/xhtml+xml'
//);
//print htmlspecialchars($xhtmlIso->markup());
//print var_dump($xhtmlIsoNoCharset->markup());
//print var_dump($xhtmlIsoNoCharset->markup(
//	$xhtmlIsoNoCharset->document->getElementsByTagName('p')
//));
//print var_dump($xhtmlUtf->markup());
//print var_dump($xhtmlUtf->markup(
//	$xhtmlUtf->document->getElementsByTagName('p')
//));
//print htmlspecialchars($xhtmlUtfNoCharset->markup());

/** FRAGMETNS **/

/* HTML fragment */

//$htmlFragmentUtf = new DOMDocumentWrapper(
//	file_get_contents('document-types/document-fragment-utf8.html')
//);
//$htmlFragmentUtf->markup();
//$htmlFragmentUtf->markup(
//	$htmlFragmentUtf->document->getElementsByTagName('span')
//);

/* XML fragment */

//$xmlFragmentUtf = new DOMDocumentWrapper(
//	file_get_contents('document-types/document-fragment-utf8.xml'),
//	'text/xml'nt var_dump($xhtmlFragmentUtf->document->saveXML());
//$xhtmlFragmentUtf->markup();
//$xhtmlFragmentUtf->markup(
//	$xhtmlFragmentUtf->document->getElementsByTagName('p')
//);
//);
//$xmlFragmentUtf->markup();
//$xmlFragmentUtf->markup(
//	$xmlFragmentUtf->document->getElementsByTagName('step')
//);

/* XHTML fragment */

//$xhtmlFragmentUtf = new DOMDocumentWrapper(
//	file_get_contents('document-types/document-fragment-utf8.xhtml'),
//	'application/xhtml+xml'
//);
//print var_dump($xhtmlFragmentUtf->document->saveXML());
//$xhtmlFragmentUtf->markup();
//$xhtmlFragmentUtf->markup(
//	$xhtmlFragmentUtf->document->getElementsByTagName('p')
//);

/* Test template */
//$result = pq('p:eq(1)');
//if ( $result->hasClass('newTitle') )
//	print "Test '{$testName}' PASSED :)";
//else
//	print "Test '{$testName}' <strong>FAILED</strong> !!! ";
//$result->dump();
//print "\n";