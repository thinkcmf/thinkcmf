<?php
/** @file
 * The Query Path package provides tools for manipulating a Document Object Model.
 * The two major DOMs are the XML DOM and the HTML DOM. Using Query Path, you can 
 * build, parse, search, and modify DOM documents.
 *
 * To use Query Path, this is the only file you should need to import.
 *
 * Standard usage:
 * @code
 * <?php
 * require 'QueryPath/QueryPath.php';
 * $qp = qp('#myID', '<?xml version="1.0"?><test><foo id="myID"/></test>');
 * $qp->append('<new><elements/></new>')->writeHTML();
 * ?>
 * @endcode
 *
 * The above would print (formatted for readability):
 * @code
 * <?xml version="1.0"?>
 * <test>
 *  <foo id="myID">
 *    <new>
 *      <element/>
 *    </new>
 *  </foo>
 * </test>
 * @endcode
 *
 * To learn about the functions available to a Query Path object, 
 * see {@link QueryPath}. The {@link qp()} function is used to build
 * new QueryPath objects. The documentation for that function explains the
 * wealth of arguments that the function can take.
 *
 * Included with the source code for QueryPath is a complete set of unit tests
 * as well as some example files. Those are good resources for learning about
 * how to apply QueryPath's tools. The full API documentation can be generated
 * from these files using PHPDocumentor.
 *
 * If you are interested in building extensions for QueryParser, see the 
 * {@link QueryPathExtender} class. There, you will find information on adding
 * your own tools to QueryPath.
 *
 * QueryPath also comes with a full CSS 3 selector parser implementation. If
 * you are interested in reusing that in other code, you will want to start
 * with {@link CssEventHandler.php}, which is the event interface for the parser.
 *
 * All of the code in QueryPath is licensed under either the LGPL or an MIT-like
 * license (you may choose which you prefer). All of the code is Copyright, 2009
 * by Matt Butcher.
 *
 * @author M Butcher <matt @aleph-null.tv>
 * @license http://opensource.org/licenses/lgpl-2.1.php The GNU Lesser GPL (LGPL) or an MIT-like license.
 * @see QueryPath
 * @see qp()
 * @see http://querypath.org The QueryPath home page.
 * @see http://api.querypath.org An online version of the API docs.
 * @see http://technosophos.com For how-tos and examples.
 * @copyright Copyright (c) 2009, Matt Butcher.
 * @version -UNSTABLE%
 *
 */

/** @addtogroup querypath_core Core API
 * Core classes and functions for QueryPath.
 *
 * These are the classes, objects, and functions that developers who use QueryPath
 * are likely to use. The qp() and htmlqp() functions are the best place to start,
 * while most of the frequently used methods are part of the QueryPath object.
 */

/** @addtogroup querypath_util Utilities
 * Utility classes for QueryPath.
 *
 * These classes add important, but less-often used features to QueryPath. Some of
 * these are used transparently (QueryPathIterator). Others you can use directly in your 
 * code (QueryPathEntities).
 */

/*   * @namespace QueryPath
 * The core classes that compose QueryPath.
 *
 * The QueryPath classes contain the brunt of the QueryPath code. If you are 
 * interested in working with just the CSS engine, you may want to look at CssEventHandler,
 * which can be used without the rest of QueryPath. If you are interested in looking 
 * carefully at QueryPath's implementation details, then the QueryPath class is where you 
 * should begin. If you are interested in writing extensions, than you may want to look at
 * QueryPathExtension, and also at some of the simple extensions, such as QPXML.
 */
 
/**
 * Regular expression for checking whether a string looks like XML.
 * @deprecated This is no longer used in QueryPath.
 */
define('ML_EXP','/^[^<]*(<(.|\s)+>)[^>]*$/');

/**
 * The CssEventHandler interfaces with the CSS parser.
 */
require_once 'CssEventHandler.php';
/**
 * The extender is used to provide support for extensions.
 */
require_once 'QueryPathExtension.php';

/**
 * Build a new Query Path.
 * This builds a new Query Path object. The new object can be used for 
 * reading, search, and modifying a document.
 *
 * While it is permissible to directly create new instances of a QueryPath
 * implementation, it is not advised. Instead, you should use this function
 * as a factory.
 *
 * Example:
 * @code
 * <?php
 * qp(); // New empty QueryPath
 * qp('path/to/file.xml'); // From a file
 * qp('<html><head></head><body></body></html>'); // From HTML or XML
 * qp(QueryPath::XHTML_STUB); // From a basic HTML document.
 * qp(QueryPath::XHTML_STUB, 'title'); // Create one from a basic HTML doc and position it at the title element.
 *
 * // Most of the time, methods are chained directly off of this call.
 * qp(QueryPath::XHTML_STUB, 'body')->append('<h1>Title</h1>')->addClass('body-class');
 * ?>
 * @endcode
 *
 * This function is used internally by QueryPath. Anything that modifies the
 * behavior of this function may also modify the behavior of common QueryPath
 * methods.
 *
 * <b>Types of documents that QueryPath can support</b>
 *
 *  qp() can take any of these as its first argument:
 *
 *  - A string of XML or HTML (See {@link XHTML_STUB})
 *  - A path on the file system or a URL
 *  - A {@link DOMDocument} object
 *  - A {@link SimpleXMLElement} object.
 *  - A {@link DOMNode} object.
 *  - An array of {@link DOMNode} objects (generally {@link DOMElement} nodes).
 *  - Another {@link QueryPath} object.
 *
 * Keep in mind that most features of QueryPath operate on elements. Other 
 * sorts of DOMNodes might not work with all features.
 *
 * <b>Supported Options</b>
 *  - context: A stream context object. This is used to pass context info
 *    to the underlying file IO subsystem.
 *  - encoding: A valid character encoding, such as 'utf-8' or 'ISO-8859-1'.
 *    The default is system-dependant, typically UTF-8. Note that this is 
 *    only used when creating new documents, not when reading existing content.
 *    (See convert_to_encoding below.)
 *  - parser_flags: An OR-combined set of parser flags. The flags supported
 *    by the DOMDocument PHP class are all supported here.
 *  - omit_xml_declaration: Boolean. If this is TRUE, then certain output
 *    methods (like {@link QueryPath::xml()}) will omit the XML declaration
 *    from the beginning of a document.
 *  - replace_entities: Boolean. If this is TRUE, then any of the insertion
 *    functions (before(), append(), etc.) will replace named entities with
 *    their decimal equivalent, and will replace un-escaped ampersands with 
 *    a numeric entity equivalent.
 *  - ignore_parser_warnings: Boolean. If this is TRUE, then E_WARNING messages
 *    generated by the XML parser will not cause QueryPath to throw an exception.
 *    This is useful when parsing
 *    badly mangled HTML, or when failure to find files should not result in 
 *    an exception. By default, this is FALSE -- that is, parsing warnings and 
 *    IO warnings throw exceptions.
 *  - convert_to_encoding: Use the MB library to convert the document to the 
 *    named encoding before parsing. This is useful for old HTML (set it to
 *    iso-8859-1 for best results). If this is not supplied, no character set 
 *    conversion will be performed. See {@link mb_convert_encoding()}.
 *    (QueryPath 1.3 and later)
 *  - convert_from_encoding: If 'convert_to_encoding' is set, this option can be
 *    used to explicitly define what character set the source document is using.
 *    By default, QueryPath will allow the MB library to guess the encoding.
 *    (QueryPath 1.3 and later)
 *  - strip_low_ascii: If this is set to TRUE then markup will have all low ASCII
 *    characters (<32) stripped out before parsing. This is good in cases where 
 *    icky HTML has (illegal) low characters in the document.
 *  - use_parser: If 'xml', Parse the document as XML. If 'html', parse the 
 *    document as HTML. Note that the XML parser is very strict, while the 
 *    HTML parser is more lenient, but does enforce some of the DTD/Schema.
 *    <i>By default, QueryPath autodetects the type.</i>
 *  - escape_xhtml_js_css_sections: XHTML needs script and css sections to be
 *    escaped. Yet older readers do not handle CDATA sections, and comments do not
 *    work properly (for numerous reasons). By default, QueryPath's *XHTML methods
 *    will wrap a script body with a CDATA declaration inside of C-style comments.
 *    If you want to change this, you can set this option with one of the 
 *    JS_CSS_ESCAPE_* constants, or you can write your own.
 *  - QueryPath_class: (ADVANCED) Use this to set the actual classname that
 *    {@link qp()} loads as a QueryPath instance. It is assumed that the 
 *    class is either {@link QueryPath} or a subclass thereof. See the test 
 *    cases for an example.
 *
 * @ingroup querypath_core
 * @param mixed $document
 *  A document in one of the forms listed above.
 * @param string $string 
 *  A CSS 3 selector.
 * @param array $options
 *  An associative array of options. Currently supported options are listed above.
 * @return QueryPath
 */
function qp($document = NULL, $string = NULL, $options = array()) {
  
  $qpClass = isset($options['QueryPath_class']) ? $options['QueryPath_class'] : 'QueryPath';
  
  $qp = new $qpClass($document, $string, $options);
  return $qp;
}

/**
 * A special-purpose version of {@link qp()} designed specifically for HTML.
 *
 * XHTML (if valid) can be easily parsed by {@link qp()} with no problems. However,
 * because of the way that libxml handles HTML, there are several common steps that
 * need to be taken to reliably parse non-XML HTML documents. This function is
 * a convenience tool for configuring QueryPath to parse HTML.
 *
 * The following options are automatically set unless overridden:
 *  - ignore_parser_warnings: TRUE
 *  - convert_to_encoding: ISO-8859-1 (the best for the HTML parser).
 *  - convert_from_encoding: auto (autodetect encoding)
 *  - use_parser: html
 *
 * Parser warning messages are also suppressed, so if the parser emits a warning,
 * the application will not be notified. This is equivalent to 
 * calling @code@qp()@endcode.
 *
 * Warning: Character set conversions will only work if the Multi-Byte (mb) library
 * is installed and enabled. This is usually enabled, but not always.
 *
 * @ingroup querypath_core
 * @see qp()
 */
function htmlqp($document = NULL, $selector = NULL, $options = array()) {

  // Need a way to force an HTML parse instead of an XML parse when the 
  // doctype is XHTML, since many XHTML documents are not valid XML
  // (because of coding errors, not by design).
  
  $options += array(
    'ignore_parser_warnings' => TRUE,
    'convert_to_encoding' => 'ISO-8859-1',
    'convert_from_encoding' => 'auto',
    //'replace_entities' => TRUE,
    'use_parser' => 'html',
    // This is stripping actually necessary low ASCII.
    //'strip_low_ascii' => TRUE,
  );
  return @qp($document, $selector, $options);
}

/**
 * The Query Path object is the primary tool in this library.
 *
 * To create a new Query Path, use the {@link qp()} function.
 *
 * If you are new to these documents, start at the {@link QueryPath.php} page.
 * There you will find a quick guide to the tools contained in this project.
 *
 * A note on serialization: QueryPath uses DOM classes internally, and those
 * do not serialize well at all. In addition, QueryPath may contain many
 * extensions, and there is no guarantee that extensions can serialize. The
 * moral of the story: Don't serialize QueryPath.
 *
 * @see qp()
 * @see QueryPath.php
 * @ingroup querypath_core
 */
class QueryPath implements IteratorAggregate, Countable {
  
  /**
   * The version string for this version of QueryPath.
   *
   * Standard releases will be of the following form: <MAJOR>.<MINOR>[.<PATCH>][-STABILITY].
   *
   * Examples:
   * - 2.0
   * - 2.1.1
   * - 2.0-alpha1
   *
   * Developer releases will always be of the form dev-<DATE>.
   *
   * @since 2.0
   */
  const VERSION = '-UNSTABLE%';
  
  /**
   * This is a stub HTML 4.01 document.
   *
   * <b>Using {@link QueryPath::XHTML_STUB} is preferred.</b>
   *
   * This is primarily for generating legacy HTML content. Modern web applications
   * should use {@link QueryPath::XHTML_STUB}.
   *
   * Use this stub with the HTML familiy of methods ({@link html()}, 
   * {@link writeHTML()}, {@link innerHTML()}).
   */
  const HTML_STUB = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
  <html lang="en">
  <head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>Untitled</title>
  </head>
  <body></body>
  </html>';
  
  /**
   * This is a stub XHTML document.
   * 
   * Since XHTML is an XML format, you should use XML functions with this document
   * fragment. For example, you should use {@link xml()}, {@link innerXML()}, and 
   * {@link writeXML()}.
   * 
   * This can be passed into {@link qp()} to begin a new basic HTML document.
   *
   * Example:
   * @code
   * $qp = qp(QueryPath::XHTML_STUB); // Creates a new XHTML document
   * $qp->writeXML(); // Writes the document as well-formed XHTML.
   * @endcode
   * @since 2.0
   */
  const XHTML_STUB = '<?xml version="1.0"?>
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml">
  <head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Untitled</title>
  </head>
  <body></body>
  </html>';
  
  /**
   * Default parser flags.
   *
   * These are flags that will be used if no global or local flags override them.
   * @since 2.0
   */
  const DEFAULT_PARSER_FLAGS = NULL;
  
  const JS_CSS_ESCAPE_CDATA = '\\1';
  const JS_CSS_ESCAPE_CDATA_CCOMMENT = '/* \\1 */';
  const JS_CSS_ESCAPE_CDATA_DOUBLESLASH = '// \\1';
  const JS_CSS_ESCAPE_NONE = '';
  
  //const IGNORE_ERRORS = 1544; //E_NOTICE | E_USER_WARNING | E_USER_NOTICE;
  private $errTypes = 771; //E_ERROR; | E_USER_ERROR;
  
  /**
   * The base DOMDocument.
   */
  protected $document = NULL;
  private $options = array(
    'parser_flags' => NULL,
    'omit_xml_declaration' => FALSE,
    'replace_entities' => FALSE,
    'exception_level' => 771, // E_ERROR | E_USER_ERROR | E_USER_WARNING | E_WARNING
    'ignore_parser_warnings' => FALSE,
    'escape_xhtml_js_css_sections' => self::JS_CSS_ESCAPE_CDATA_CCOMMENT,
  );
  /**
   * The array of matches.
   */
  protected $matches = array();
  /**
   * The last array of matches.
   */
  protected $last = array(); // Last set of matches.
  private $ext = array(); // Extensions array.
  
  /**
   * The number of current matches.
   *
   * @see count()
   */
  public $length = 0;
  
  /**
   * Constructor.
   *
   * This should not be called directly. Use the {@link qp()} factory function
   * instead.
   *
   * @param mixed $document 
   *   A document-like object.
   * @param string $string
   *   A CSS 3 Selector
   * @param array $options
   *   An associative array of options.
   * @see qp()
   */
  public function __construct($document = NULL, $string = NULL, $options = array()) {
    $string = trim($string);
    $this->options = $options + QueryPathOptions::get() + $this->options;
    
    $parser_flags = isset($options['parser_flags']) ? $options['parser_flags'] : self::DEFAULT_PARSER_FLAGS;
    if (!empty($this->options['ignore_parser_warnings'])) {
      // Don't convert parser warnings into exceptions.
      $this->errTypes = 257; //E_ERROR | E_USER_ERROR;
    }
    elseif (isset($this->options['exception_level'])) {
      // Set the error level at which exceptions will be thrown. By default, 
      // QueryPath will throw exceptions for 
      // E_ERROR | E_USER_ERROR | E_WARNING | E_USER_WARNING.
      $this->errTypes = $this->options['exception_level'];
    }
    
    // Empty: Just create an empty QP.
    if (empty($document)) {
      $this->document = isset($this->options['encoding']) ? new DOMDocument('1.0', $this->options['encoding']) : new DOMDocument();
      $this->setMatches(new SplObjectStorage());
    }
    // Figure out if document is DOM, HTML/XML, or a filename
    elseif (is_object($document)) {
      
      if ($document instanceof QueryPath) {
        $this->matches = $document->get(NULL, TRUE);
        if ($this->matches->count() > 0)
          $this->document = $this->getFirstMatch()->ownerDocument;
      }
      elseif ($document instanceof DOMDocument) {
        $this->document = $document;
        //$this->matches = $this->matches($document->documentElement);
        $this->setMatches($document->documentElement);
      }
      elseif ($document instanceof DOMNode) {
        $this->document = $document->ownerDocument;
        //$this->matches = array($document);
        $this->setMatches($document);
      }
      elseif ($document instanceof SimpleXMLElement) {
        $import = dom_import_simplexml($document);
        $this->document = $import->ownerDocument;
        //$this->matches = array($import);
        $this->setMatches($import);
      }
      elseif ($document instanceof SplObjectStorage) {
        if ($document->count() == 0) {
          throw new QueryPathException('Cannot initialize QueryPath from an empty SplObjectStore');
        }
        $this->matches = $document;
        $this->document = $this->getFirstMatch()->ownerDocument;
      }
      else {
        throw new QueryPathException('Unsupported class type: ' . get_class($document));
      }
    }
    elseif (is_array($document)) {
      //trigger_error('Detected deprecated array support', E_USER_NOTICE);
      if (!empty($document) && $document[0] instanceof DOMNode) {
        $found = new SplObjectStorage();
        foreach ($document as $item) $found->attach($item);
        //$this->matches = $found;
        $this->setMatches($found);
        $this->document = $this->getFirstMatch()->ownerDocument;
      }
    }
    elseif ($this->isXMLish($document)) {
      // $document is a string with XML
      $this->document = $this->parseXMLString($document);
      $this->setMatches($this->document->documentElement);
    }
    else {
      
      // $document is a filename
      $context = empty($options['context']) ? NULL : $options['context'];
      $this->document = $this->parseXMLFile($document, $parser_flags, $context);
      $this->setMatches($this->document->documentElement);
    }
    
    // Do a find if the second param was set.
    if (isset($string) && strlen($string) > 0) {
      $this->find($string);
    }
  }
  
  /**
   * A static function for transforming data into a Data URL.
   *
   * This can be used to create Data URLs for injection into CSS, JavaScript, or other 
   * non-XML/HTML content. If you are working with QP objects, you may want to use
   * {@link dataURL()} instead.
   *
   * @param mixed $data
   *  The contents to inject as the data. The value can be any one of the following:
   *  - A URL: If this is given, then the subsystem will read the content from that URL. THIS 
   *    MUST BE A FULL URL, not a relative path.
   *  - A string of data: If this is given, then the subsystem will encode the string.
   *  - A stream or file handle: If this is given, the stream's contents will be encoded
   *    and inserted as data.
   *  (Note that we make the assumption here that you would never want to set data to be
   *  a URL. If this is an incorrect assumption, file a bug.)
   * @param string $mime
   *  The MIME type of the document.
   * @param resource $context
   *  A valid context. Use this only if you need to pass a stream context. This is only necessary
   *  if $data is a URL. (See {@link stream_context_create()}).
   * @return 
   *  An encoded data URL.
   */
  public static function encodeDataURL($data, $mime = 'application/octet-stream', $context = NULL) {
    if (is_resource($data)) {
      $data = stream_get_contents($data);
    }
    elseif (filter_var($data, FILTER_VALIDATE_URL)) {
      $data = file_get_contents($data, FALSE, $context);
    }
    
    $encoded = base64_encode($data);
    
    return 'data:' . $mime . ';base64,' . $encoded;
  }
  
  /**
   * Get the effective options for the current QueryPath object.
   *
   * This returns an associative array of all of the options as set
   * for the current QueryPath object. This includes default options,
   * options directly passed in via {@link qp()} or the constructor,
   * an options set in the {@link QueryPathOptions} object.
   *
   * The order of merging options is this:
   *  - Options passed in using {@link qp()} are highest priority, and will
   *    override other options.
   *  - Options set with {@link QueryPathOptions} will override default options,
   *    but can be overridden by options passed into {@link qp()}.
   *  - Default options will be used when no overrides are present.
   *
   * This function will return the options currently used, with the above option
   * overriding having been calculated already.
   *
   * @return array
   *  An associative array of options, calculated from defaults and overridden 
   *  options. 
   * @see qp()
   * @see QueryPathOptions::set()
   * @see QueryPathOptions::merge()
   * @since 2.0
   */
  public function getOptions() {
    return $this->options;
  }
  
  /**
   * Select the root element of the document.
   *
   * This sets the current match to the document's root element. For 
   * practical purposes, this is the same as:
   * @code
   * qp($someDoc)->find(':root');
   * @endcode
   * However, since it doesn't invoke a parser, it has less overhead. It also 
   * works in cases where the QueryPath has been reduced to zero elements (a
   * case that is not handled by find(':root') because there is no element
   * whose root can be found).
   *
   * @param string $selector
   *  A selector. If this is supplied, QueryPath will navigate to the 
   *  document root and then run the query. (Added in QueryPath 2.0 Beta 2) 
   * @return QueryPath
   *  The QueryPath object, wrapping the root element (document element)
   *  for the current document.
   */
  public function top($selector = NULL) {
    $this->setMatches($this->document->documentElement);
    // print '=====================' . PHP_EOL;
    // var_dump($this->document);
    // print '=====================' . PHP_EOL;
    return !empty($selector) ? $this->find($selector) : $this;
  }
  
  /**
   * Given a CSS Selector, find matching items.
   *
   * @param string $selector
   *   CSS 3 Selector
   * @return QueryPath
   * @see filter()
   * @see is()
   * @todo If a find() returns zero matches, then a subsequent find() will
   *  also return zero matches, even if that find has a selector like :root.
   *  The reason for this is that the {@link QueryPathCssEventHandler} does
   *  not set the root of the document tree if it cannot find any elements
   *  from which to determine what the root is. The workaround is to use 
   *  {@link top()} to select the root element again.
   */
  public function find($selector) {
    
    // Optimize for ID/Class searches. These two take a long time
    // when a rdp is used. Using an XPath pushes work to C code.
    $ids = array();
    
    $regex = '/^#([\w-]+)$|^\.([\w-]+)$/'; // $1 is ID, $2 is class.
    //$regex = '/^#([\w-]+)$/';
    if (preg_match($regex, $selector, $ids) === 1) {
      // If $1 is a match, we have an ID.
      if (!empty($ids[1])) {
        $xpath = new DOMXPath($this->document);
        foreach ($this->matches as $item) {
          
          // For whatever reasons, the .// does not work correctly
          // if the selected element is the root element. So we have
          // an awful hack.
          if ($item->isSameNode($this->document->documentElement) ) {
            $xpathQuery = "//*[@id='{$ids[1]}']";
          }
          // This is the correct XPath query.
          else {
            $xpathQuery = ".//*[@id='{$ids[1]}']";
          }
          //$nl = $xpath->query("//*[@id='{$ids[1]}']", $item);
          //$nl = $xpath->query(".//*[@id='{$ids[1]}']", $item);
          $nl = $xpath->query($xpathQuery, $item);
          if ($nl->length > 0) {
            $this->setMatches($nl->item(0));
            break;
          }
          else {
            // If no match is found, we set an empty.
            $this->noMatches();
          }
        }
      }
      // Quick search for class values. While the XPath can't do it
      // all, it is faster than doing a recusive node search.
      else {
        $xpath = new DOMXPath($this->document);
        $found = new SplObjectStorage();
        foreach ($this->matches as $item) {
          // See comments on this in the #id code above.
          if ($item->isSameNode($this->document->documentElement) ) {
            $xpathQuery = "//*[@class]";
          }
          // This is the correct XPath query.
          else {
            $xpathQuery = ".//*[@class]";
          }
          $nl = $xpath->query($xpathQuery, $item);
          for ($i = 0; $i < $nl->length; ++$i) {
            $vals = explode(' ', $nl->item($i)->getAttribute('class'));
            if (in_array($ids[2], $vals)) $found->attach($nl->item($i));
          }
        }
        $this->setMatches($found);
      }
      
      return $this;
    }
    
    
    $query = new QueryPathCssEventHandler($this->matches);
    $query->find($selector);
    //$this->matches = $query->getMatches();
    $this->setMatches($query->getMatches());
    return $this;
  }
  
  /**
   * Execute an XPath query and store the results in the QueryPath.
   *
   * Most methods in this class support CSS 3 Selectors. Sometimes, though,
   * XPath provides a finer-grained query language. Use this to execute
   * XPath queries.
   *
   * Beware, though. QueryPath works best on DOM Elements, but an XPath 
   * query can return other nodes, strings, and values. These may not work with
   * other QueryPath functions (though you will be able to access the
   * values with {@link get()}).
   *
   * @param string $query
   *  An XPath query.
   * @param array $options
   *  Currently supported options are:
   *   - 'namespace_prefix': And XML namespace prefix to be used as the default. Used 
   *      in conjunction with 'namespace_uri'
   *   - 'namespace_uri': The URI to be used as the default namespace URI. Used 
   *      with 'namespace_prefix'
   * @return QueryPath 
   *  A QueryPath object wrapping the results of the query.
   * @see find()
   * @author M Butcher
   * @author Xavier Prud'homme
   */
  public function xpath($query, $options = array()) {
    $xpath = new DOMXPath($this->document);
    
    // Register a default namespace.
    if (!empty($options['namespace_prefix']) && !empty($options['namespace_uri'])) {
      $xpath->registerNamespace($options['namespace_prefix'], $options['namespace_uri']);
    }
    
    $found = new SplObjectStorage();
    foreach ($this->matches as $item) {
      $nl = $xpath->query($query, $item);
      if ($nl->length > 0) {
        for ($i = 0; $i < $nl->length; ++$i) $found->attach($nl->item($i));
      }
    }
    $this->setMatches($found);
    return $this;
  }
  
  /**
   * Get the number of elements currently wrapped by this object.
   *
   * Note that there is no length property on this object.
   *
   * @return int
   *  Number of items in the object.
   * @deprecated QueryPath now implements Countable, so use count().
   */
  public function size() {
    return $this->matches->count();
  }
  
  /**
   * Get the number of elements currently wrapped by this object.
   *
   * Since QueryPath is Countable, the PHP count() function can also
   * be used on a QueryPath.
   *
   * @code
   * <?php
   *  count(qp($xml, 'div'));
   * ?>
   * @endcode
   *
   * @return int
   *  The number of matches in the QueryPath.
   */
  public function count() {
    return $this->matches->count();
  }
  
  /**
   * Get one or all elements from this object.
   *
   * When called with no paramaters, this returns all objects wrapped by 
   * the QueryPath. Typically, these are DOMElement objects (unless you have
   * used {@link map()}, {@link xpath()}, or other methods that can select
   * non-elements).
   *
   * When called with an index, it will return the item in the QueryPath with 
   * that index number.
   *
   * Calling this method does not change the QueryPath (e.g. it is 
   * non-destructive).
   *
   * You can use qp()->get() to iterate over all elements matched. You can
   * also iterate over qp() itself (QueryPath implementations must be Traversable). 
   * In the later case, though, each item 
   * will be wrapped in a QueryPath object. To learn more about iterating
   * in QueryPath, see {@link examples/techniques.php}.
   *
   * @param int $index
   *   If specified, then only this index value will be returned. If this 
   *   index is out of bounds, a NULL will be returned.
   * @param boolean $asObject
   *   If this is TRUE, an {@link SplObjectStorage} object will be returned 
   *   instead of an array. This is the preferred method for extensions to use.
   * @return mixed
   *   If an index is passed, one element will be returned. If no index is
   *   present, an array of all matches will be returned.
   * @see eq()
   * @see SplObjectStorage
   */
  public function get($index = NULL, $asObject = FALSE) {
    if (isset($index)) {
      return ($this->size() > $index) ? $this->getNthMatch($index) : NULL;
    }
    // Retain support for legacy.
    if (!$asObject) {
      $matches = array();
      foreach ($this->matches as $m) $matches[] = $m;
      return $matches;
    }
    return $this->matches;
  }
  
  /**
   * Get the DOMDocument that we currently work with.
   *
   * This returns the current DOMDocument. Any changes made to this document will be
   * accessible to QueryPath, as both will share access to the same object.
   * 
   * @return DOMDocument
   */
  public function document() {
    return $this->document;
  }
  
  /**
   * On an XML document, load all XIncludes.
   *
   * @return QueryPath
   */
  public function xinclude() {
    $this->document->xinclude();
    return $this;
  }
  
  /**
   * Get all current elements wrapped in an array.
   * Compatibility function for jQuery 1.4, but identical to calling {@link get()}
   * with no parameters.
   *
   * @return array
   *  An array of DOMNodes (typically DOMElements).
   */
  public function toArray() {
    return $this->get();
  }
  /**
   * Get/set an attribute.
   * - If no parameters are specified, this returns an associative array of all 
   *   name/value pairs.
   * - If both $name and $value are set, then this will set the attribute name/value
   *   pair for all items in this object. 
   * - If $name is set, and is an array, then
   *   all attributes in the array will be set for all items in this object.
   * - If $name is a string and is set, then the attribute value will be returned.
   *
   * When an attribute value is retrieved, only the attribute value of the FIRST
   * match is returned.
   *
   * @param mixed $name
   *   The name of the attribute or an associative array of name/value pairs.
   * @param string $value
   *   A value (used only when setting an individual property).
   * @return mixed
   *   If this was a setter request, return the QueryPath object. If this was
   *   an access request (getter), return the string value.
   * @see removeAttr()
   * @see tag()
   * @see hasAttr()
   * @see hasClass()
   */
  public function attr($name = NULL, $value = NULL) {
    
    // Default case: Return all attributes as an assoc array.
    if (is_null($name)) {
      if ($this->matches->count() == 0) return NULL;
      $ele = $this->getFirstMatch();
      $buffer = array();
      
      // This does not appear to be part of the DOM
      // spec. Nor is it documented. But it works.
      foreach ($ele->attributes as $name => $attrNode) {
        $buffer[$name] = $attrNode->value;
      }
      return $buffer;
    }
    
    // multi-setter
    if (is_array($name)) {
      foreach ($name as $k => $v) {
        foreach ($this->matches as $m) $m->setAttribute($k, $v);
      }
      return $this;
    }
    // setter
    if (isset($value)) {
      foreach ($this->matches as $m) $m->setAttribute($name, $value);
      return $this;
    }
    
    //getter
    if ($this->matches->count() == 0) return NULL;
    
    // Special node type handler:
    if ($name == 'nodeType') {
      return $this->getFirstMatch()->nodeType;
    }
    
    // Always return first match's attr.
    return $this->getFirstMatch()->getAttribute($name);
  }
  /**
   * Check to see if the given attribute is present.
   *
   * This returns TRUE if <em>all</em> selected items have the attribute, or 
   * FALSE if at least one item does not have the attribute.
   *
   * @param string $attrName
   *  The attribute name.
   * @return boolean
   *  TRUE if all matches have the attribute, FALSE otherwise.
   * @since 2.0
   * @see attr()
   * @see hasClass()
   */
  public function hasAttr($attrName) {
    foreach ($this->matches as $match) {
      if (!$match->hasAttribute($attrName)) return FALSE;
    }
    return TRUE;
  }
  
  /**
   * Set/get a CSS value for the current element(s).
   * This sets the CSS value for each element in the QueryPath object.
   * It does this by setting (or getting) the style attribute (without a namespace).
   *
   * For example, consider this code:
   * @code
   * <?php
   * qp(HTML_STUB, 'body')->css('background-color','red')->html();
   * ?>
   * @endcode
   * This will return the following HTML:
   * @code
   * <body style="background-color: red"/>
   * @endcode
   *
   * If no parameters are passed into this function, then the current style
   * element will be returned unparsed. Example:
   * @code
   * <?php
   * qp(HTML_STUB, 'body')->css('background-color','red')->css();
   * ?>
   * @endcode
   * This will return the following:
   * @code
   * background-color: red
   * @endcode
   *
   * As of QueryPath 2.1, existing style attributes will be merged with new attributes.
   * (In previous versions of QueryPath, a call to css() overwrite the existing style
   * values).
   *
   * @param mixed $name
   *  If this is a string, it will be used as a CSS name. If it is an array,
   *  this will assume it is an array of name/value pairs of CSS rules. It will
   *  apply all rules to all elements in the set.
   * @param string $value
   *  The value to set. This is only set if $name is a string.
   * @return QueryPath
   */
  public function css($name = NULL, $value = '') {
    if (empty($name)) {
      return $this->attr('style');
    }
    
    // Get any existing CSS.
    $css = array();
    foreach ($this->matches as $match) {
      $style = $match->getAttribute('style');
      if (!empty($style)) {
        // XXX: Is this sufficient?
        $style_array = explode(';', $style);
        foreach ($style_array as $item) {
          $item = trim($item);
          
          // Skip empty attributes.
          if (strlen($item) == 0) continue;
          
          list($css_att, $css_val) = explode(':',$item, 2);
          $css[$css_att] = trim($css_val);
        }
      }
    }
    
    if (is_array($name)) {
      // Use array_merge instead of + to preserve order.
      $css = array_merge($css, $name);
    }
    else {
      $css[$name] = $value;
    }
    
    // Collapse CSS into a string.
    $format = '%s: %s;';
    $css_string = '';
    foreach ($css as $n => $v) {
      $css_string .= sprintf($format, $n, trim($v));
    }
    
    $this->attr('style', $css_string);
    return $this;
  }
  
  /**
   * Insert or retrieve a Data URL.
   *
   * When called with just $attr, it will fetch the result, attempt to decode it, and
   * return an array with the MIME type and the application data.
   *
   * When called with both $attr and $data, it will inject the data into all selected elements
   * So @code$qp->dataURL('src', file_get_contents('my.png'), 'image/png')@endcode will inject 
   * the given PNG image into the selected elements.
   *
   * The current implementation only knows how to encode and decode Base 64 data.
   *
   * Note that this is known *not* to work on IE 6, but should render fine in other browsers.
   *
   * @param string $attr
   *  The name of the attribute.
   * @param mixed $data
   *  The contents to inject as the data. The value can be any one of the following:
   *  - A URL: If this is given, then the subsystem will read the content from that URL. THIS 
   *    MUST BE A FULL URL, not a relative path.
   *  - A string of data: If this is given, then the subsystem will encode the string.
   *  - A stream or file handle: If this is given, the stream's contents will be encoded
   *    and inserted as data.
   *  (Note that we make the assumption here that you would never want to set data to be
   *  a URL. If this is an incorrect assumption, file a bug.)
   * @param string $mime
   *  The MIME type of the document.
   * @param resource $context
   *  A valid context. Use this only if you need to pass a stream context. This is only necessary
   *  if $data is a URL. (See {@link stream_context_create()}).
   * @return
   *  If this is called as a setter, this will return a QueryPath object. Otherwise, it
   *  will attempt to fetch data out of the attribute and return that.
   * @see http://en.wikipedia.org/wiki/Data:_URL
   * @see attr()
   * @since 2.1
   */
  public function dataURL($attr, $data = NULL, $mime = 'application/octet-stream', $context = NULL) {
    if (is_null($data)) {
      // Attempt to fetch the data
      $data = $this->attr($attr);
      if (empty($data) || is_array($data) || strpos($data, 'data:') !== 0) {
        return;
      }
      
      // So 1 and 2 should be MIME types, and 3 should be the base64-encoded data.
      $regex = '/^data:([a-zA-Z0-9]+)\/([a-zA-Z0-9]+);base64,(.*)$/';
      $matches = array();
      preg_match($regex, $data, $matches);
      
      if (!empty($matches)) {
        $result = array(
          'mime' => $matches[1] . '/' . $matches[2],
          'data' => base64_decode($matches[3]),
        );
        return $result;
      }
    }
    else {
      
      $attVal = self::encodeDataURL($data, $mime, $context);
      
      return $this->attr($attr, $attVal);
      
    }
  }
  

  
  /**
   * Remove the named attribute from all elements in the current QueryPath.
   *
   * This will remove any attribute with the given name. It will do this on each
   * item currently wrapped by QueryPath.
   *
   * As is the case in jQuery, this operation is not considered destructive.
   *
   * @param string $name
   *  Name of the parameter to remove.
   * @return QueryPath
   *  The QueryPath object with the same elements.
   * @see attr()
   */
  public function removeAttr($name) {
    foreach ($this->matches as $m) {
      //if ($m->hasAttribute($name))
        $m->removeAttribute($name);
    }
    return $this;
  }
  /**
   * Reduce the matched set to just one.
   *
   * This will take a matched set and reduce it to just one item -- the item 
   * at the index specified. This is a destructive operation, and can be undone
   * with {@link end()}.
   *
   * @param $index
   *  The index of the element to keep. The rest will be 
   *  discarded.
   * @return QueryPath
   * @see get()
   * @see is()
   * @see end()
   */
  public function eq($index) {
    // XXX: Might there be a more efficient way of doing this?
    $this->setMatches($this->getNthMatch($index));
    return $this;
  }
  /**
   * Given a selector, this checks to see if the current set has one or more matches.
   *
   * Unlike jQuery's version, this supports full selectors (not just simple ones).
   *
   * @param string $selector
   *   The selector to search for. As of QueryPath 2.1.1, this also supports passing a
   *   DOMNode object.
   * @return boolean
   *   TRUE if one or more elements match. FALSE if no match is found.
   * @see get()
   * @see eq()
   */
  public function is($selector) {
    
    if (is_object($selector)) {
      if ($selector instanceof DOMNode) {
        return count($this->matches) == 1 && $selector->isSameNode($this->get(0));
      }
      elseif ($selector instanceof Traversable) {
        if (count($selector) != count($this->matches)) {
          return FALSE;
        }
        // Without $seen, there is an edge case here if $selector contains the same object
        // more than once, but the counts are equal. For example, [a, a, a, a] will
        // pass an is() on [a, b, c, d]. We use the $seen SPLOS to prevent this.
        $seen = new SplObjectStorage();
        foreach ($selector as $item) {
          if (!$this->matches->contains($item) || $seen->contains($item)) {
            return FALSE;
          }
          $seen->attach($item);
        }
        return TRUE;
      }
      throw new Exception('Cannot compare an object to a QueryPath.');
      return FALSE;
    }
    
    foreach ($this->matches as $m) {
      $q = new QueryPathCssEventHandler($m);
      if ($q->find($selector)->getMatches()->count()) {
        return TRUE;
      }
    }
    return FALSE;
  }
  /**
   * Filter a list down to only elements that match the selector.
   * Use this, for example, to find all elements with a class, or with 
   * certain children.
   *
   * @param string $selector
   *   The selector to use as a filter.
   * @return QueryPath
   *   The QueryPath with non-matching items filtered out.
   * @see filterLambda()
   * @see filterCallback()
   * @see map()
   * @see find()
   * @see is()
   */
  public function filter($selector) {
    $found = new SplObjectStorage();
    foreach ($this->matches as $m) if (qp($m, NULL, $this->options)->is($selector)) $found->attach($m);
    $this->setMatches($found);
    return $this;
  }
  /**
   * Filter based on a lambda function.
   *
   * The function string will be executed as if it were the body of a 
   * function. It is passed two arguments:
   * - $index: The index of the item.
   * - $item: The current Element.
   * If the function returns boolean FALSE, the item will be removed from
   * the list of elements. Otherwise it will be kept.
   *
   * Example:
   * @code
   * qp('li')->filterLambda('qp($item)->attr("id") == "test"');
   * @endcode
   *
   * The above would filter down the list to only an item whose ID is
   * 'text'.
   *
   * @param string $fn
   *  Inline lambda function in a string.
   * @return QueryPath
   * @see filter()
   * @see map()
   * @see mapLambda()
   * @see filterCallback()
   */
  public function filterLambda($fn) {
    $function = create_function('$index, $item', $fn);
    $found = new SplObjectStorage();
    $i = 0;
    foreach ($this->matches as $item)
      if ($function($i++, $item) !== FALSE) $found->attach($item);
    
    $this->setMatches($found);
    return $this;
  }
  
  /**
   * Use regular expressions to filter based on the text content of matched elements.
   *
   * Only items that match the given regular expression will be kept. All others will
   * be removed.
   *
   * The regular expression is run against the <i>text content</i> (the PCDATA) of the 
   * elements. This is a way of filtering elements based on their content. 
   *
   * Example:
   * @code
   *  <?xml version="1.0"?>
   *  <div>Hello <i>World</i></div>
   * @endcode
   *
   * @code
   *  <?php
   *    // This will be 1.
   *    qp($xml, 'div')->filterPreg('/World/')->size();
   *  ?>
   * @endcode
   *
   * The return value above will be 1 because the text content of @codeqp($xml, 'div')@endcode is
   * @codeHello World@endcode.
   *
   * Compare this to the behavior of the <em>:contains()</em> CSS3 pseudo-class.
   * 
   * @param string $regex
   *  A regular expression.
   * @return QueryPath
   * @see filter()
   * @see filterCallback()
   * @see preg_match()
   */
  public function filterPreg($regex) {
    
    $found = new SplObjectStorage();
    
    foreach ($this->matches as $item) {
      if (preg_match($regex, $item->textContent) > 0) {
        $found->attach($item);
      }
    }
    $this->setMatches($found);
    
    return $this;
  }
  /**
   * Filter based on a callback function.
   *
   * A callback may be any of the following:
   *  - a function: 'my_func'.
   *  - an object/method combo: $obj, 'myMethod'
   *  - a class/method combo: 'MyClass', 'myMethod'
   * Note that classes are passed in strings. Objects are not.
   *
   * Each callback is passed to arguments:
   *  - $index: The index position of the object in the array.
   *  - $item: The item to be operated upon.
   *
   * If the callback function returns FALSE, the item will be removed from the 
   * set of matches. Otherwise the item will be considered a match and left alone.
   *
   * @param callback $callback.
   *   A callback either as a string (function) or an array (object, method OR 
   *   classname, method).
   * @return QueryPath
   *   Query path object augmented according to the function.
   * @see filter()
   * @see filterLambda()
   * @see map()
   * @see is()
   * @see find()
   */
  public function filterCallback($callback) {
    $found = new SplObjectStorage();
    $i = 0;
    if (is_callable($callback)) {
      foreach($this->matches as $item) 
        if (call_user_func($callback, $i++, $item) !== FALSE) $found->attach($item);
    }
    else {
      throw new QueryPathException('The specified callback is not callable.');
    }
    $this->setMatches($found);
    return $this;
  }
  /**
   * Filter a list to contain only items that do NOT match.
   *
   * @param string $selector
   *  A selector to use as a negation filter. If the filter is matched, the 
   *  element will be removed from the list.
   * @return QueryPath
   *  The QueryPath object with matching items filtered out.
   * @see find()
   */
  public function not($selector) {
    $found = new SplObjectStorage();
    if ($selector instanceof DOMElement) {
      foreach ($this->matches as $m) if ($m !== $selector) $found->attach($m); 
    }
    elseif (is_array($selector)) {
      foreach ($this->matches as $m) {
        if (!in_array($m, $selector, TRUE)) $found->attach($m);
      }
    }
    elseif ($selector instanceof SplObjectStorage) {
      foreach ($this->matches as $m) if ($selector->contains($m)) $found->attach($m); 
    }
    else {
      foreach ($this->matches as $m) if (!qp($m, NULL, $this->options)->is($selector)) $found->attach($m);
    }
    $this->setMatches($found);
    return $this;
  }
  /**
   * Get an item's index.
   *
   * Given a DOMElement, get the index from the matches. This is the 
   * converse of {@link get()}.
   *
   * @param DOMElement $subject
   *  The item to match.
   * 
   * @return mixed
   *  The index as an integer (if found), or boolean FALSE. Since 0 is a 
   *  valid index, you should use strong equality (===) to test..
   * @see get()
   * @see is()
   */
  public function index($subject) {
    
    $i = 0;
    foreach ($this->matches as $m) {
      if ($m === $subject) {
        return $i;
      }
      ++$i;
    }
    return FALSE;
  }
  /**
   * Run a function on each item in a set.
   *
   * The mapping callback can return anything. Whatever it returns will be
   * stored as a match in the set, though. This means that afer a map call, 
   * there is no guarantee that the elements in the set will behave correctly
   * with other QueryPath functions.
   *
   * Callback rules:
   * - If the callback returns NULL, the item will be removed from the array.
   * - If the callback returns an array, the entire array will be stored in 
   *   the results.
   * - If the callback returns anything else, it will be appended to the array 
   *   of matches.
   *
   * @param callback $callback
   *  The function or callback to use. The callback will be passed two params:
   *  - $index: The index position in the list of items wrapped by this object.
   *  - $item: The current item.
   *
   * @return QueryPath
   *  The QueryPath object wrapping a list of whatever values were returned
   *  by each run of the callback.
   *
   * @see QueryPath::get()
   * @see filter()
   * @see find()
   */
  public function map($callback) {
    $found = new SplObjectStorage();
    
    if (is_callable($callback)) {
      $i = 0;
      foreach ($this->matches as $item) {
        $c = call_user_func($callback, $i, $item);
        if (isset($c)) {
          if (is_array($c) || $c instanceof Iterable) {
            foreach ($c as $retval) {
              if (!is_object($retval)) {
                $tmp = new stdClass();
                $tmp->textContent = $retval;
                $retval = $tmp;
              }
              $found->attach($retval);
            }
          }
          else {
            if (!is_object($c)) {
              $tmp = new stdClass();
              $tmp->textContent = $c;
              $c = $tmp;
            }
            $found->attach($c);
          }
        }
        ++$i;
      }
    }
    else {
      throw new QueryPathException('Callback is not callable.');
    }
    $this->setMatches($found, FALSE);
    return $this;
  }
  /**
   * Narrow the items in this object down to only a slice of the starting items.
   *
   * @param integer $start
   *  Where in the list of matches to begin the slice.
   * @param integer $length
   *  The number of items to include in the slice. If nothing is specified, the 
   *  all remaining matches (from $start onward) will be included in the sliced
   *  list.
   * @return QueryPath
   * @see array_slice()
   */
  public function slice($start, $length = 0) {
    $end = $length;
    $found = new SplObjectStorage();
    if ($start >= $this->size()) {
      $this->setMatches($found);
      return $this;
    }
    
    $i = $j = 0;
    foreach ($this->matches as $m) {
      if ($i >= $start) {
        if ($end > 0 && $j >= $end) {
          break;
        }
        $found->attach($m);
        ++$j;
      }
      ++$i;
    }
    
    $this->setMatches($found);
    return $this;
  }
  /**
   * Run a callback on each item in the list of items.
   *
   * Rules of the callback:
   * - A callback is passed two variables: $index and $item. (There is no 
   *   special treatment of $this, as there is in jQuery.)
   *   - You will want to pass $item by reference if it is not an
   *     object (DOMNodes are all objects).
   * - A callback that returns FALSE will stop execution of the each() loop. This
   *   works like break in a standard loop.
   * - A TRUE return value from the callback is analogous to a continue statement.
   * - All other return values are ignored.
   *
   * @param callback $callback
   *  The callback to run.
   * @return QueryPath
   *  The QueryPath.
   * @see eachLambda()
   * @see filter()
   * @see map()
   */
  public function each($callback) {
    if (is_callable($callback)) {
      $i = 0;
      foreach ($this->matches as $item) {
        if (call_user_func($callback, $i, $item) === FALSE) return $this;
        ++$i;
      }
    }
    else {
      throw new QueryPathException('Callback is not callable.');
    }
    return $this;
  }
  /**
   * An each() iterator that takes a lambda function.
   * 
   * @param string $lambda
   *  The lambda function. This will be passed ($index, &$item).
   * @return QueryPath
   *  The QueryPath object.
   * @see each()
   * @see filterLambda()
   * @see filterCallback()
   * @see map()
   */
  public function eachLambda($lambda) {
    $index = 0;
    foreach ($this->matches as $item) {
      $fn = create_function('$index, &$item', $lambda);
      if ($fn($index, $item) === FALSE) return $this;
      ++$index;
    }
    return $this;
  }
  /**
   * Insert the given markup as the last child.
   *
   * The markup will be inserted into each match in the set.
   *
   * The same element cannot be inserted multiple times into a document. DOM
   * documents do not allow a single object to be inserted multiple times 
   * into the DOM. To insert the same XML repeatedly, we must first clone
   * the object. This has one practical implication: Once you have inserted
   * an element into the object, you cannot further manipulate the original 
   * element and expect the changes to be replciated in the appended object.
   * (They are not the same -- there is no shared reference.) Instead, you
   * will need to retrieve the appended object and operate on that.
   *
   * @param mixed $data
   *  This can be either a string (the usual case), or a DOM Element.
   * @return QueryPath
   *  The QueryPath object.
   * @see appendTo()
   * @see prepend()
   * @throws QueryPathException
   *  Thrown if $data is an unsupported object type.
   */
  public function append($data) {
    $data = $this->prepareInsert($data);
    if (isset($data)) {
      if (empty($this->document->documentElement) && $this->matches->count() == 0) {
        // Then we assume we are writing to the doc root
        $this->document->appendChild($data);
        $found = new SplObjectStorage();
        $found->attach($this->document->documentElement);
        $this->setMatches($found);
      }
      else {
        // You can only append in item once. So in cases where we
        // need to append multiple times, we have to clone the node.
        foreach ($this->matches as $m) { 
          // DOMDocumentFragments are even more troublesome, as they don't
          // always clone correctly. So we have to clone their children.
          if ($data instanceof DOMDocumentFragment) {
            foreach ($data->childNodes as $n)
              $m->appendChild($n->cloneNode(TRUE));
          }
          else {
            // Otherwise a standard clone will do.
            $m->appendChild($data->cloneNode(TRUE));
          }
          
        }
      }
        
    }
    return $this;
  }
  /**
   * Append the current elements to the destination passed into the function.
   *
   * This cycles through all of the current matches and appends them to 
   * the context given in $destination. If a selector is provided then the 
   * $destination is queried (using that selector) prior to the data being
   * appended. The data is then appended to the found items.
   *
   * @param QueryPath $dest
   *  A QueryPath object that will be appended to.
   * @return QueryPath
   *  The original QueryPath, unaltered. Only the destination QueryPath will
   *  be modified.
   * @see append()
   * @see prependTo()
   * @throws QueryPathException
   *  Thrown if $data is an unsupported object type.
   */
  public function appendTo(QueryPath $dest) {
    foreach ($this->matches as $m) $dest->append($m);
    return $this;
  }
  /**
   * Insert the given markup as the first child.
   *
   * The markup will be inserted into each match in the set.
   *
   * @param mixed $data
   *  This can be either a string (the usual case), or a DOM Element.
   * @return QueryPath
   * @see append()
   * @see before()
   * @see after()
   * @see prependTo()
   * @throws QueryPathException
   *  Thrown if $data is an unsupported object type.
   */
  public function prepend($data) {
    $data = $this->prepareInsert($data);
    if (isset($data)) {
      foreach ($this->matches as $m) {
        $ins = $data->cloneNode(TRUE);
        if ($m->hasChildNodes())
          $m->insertBefore($ins, $m->childNodes->item(0));
        else
          $m->appendChild($ins);
      }
    }
    return $this;
  }
  /**
   * Take all nodes in the current object and prepend them to the children nodes of
   * each matched node in the passed-in QueryPath object.
   *
   * This will iterate through each item in the current QueryPath object and 
   * add each item to the beginning of the children of each element in the 
   * passed-in QueryPath object.
   *
   * @see insertBefore()
   * @see insertAfter()
   * @see prepend()
   * @see appendTo()
   * @param QueryPath $dest
   *  The destination QueryPath object.
   * @return QueryPath
   *  The original QueryPath, unmodified. NOT the destination QueryPath.
   * @throws QueryPathException
   *  Thrown if $data is an unsupported object type.
   */
  public function prependTo(QueryPath $dest) {
    foreach ($this->matches as $m) $dest->prepend($m);
    return $this;
  }

  /**
   * Insert the given data before each element in the current set of matches.
   *
   * This will take the give data (XML or HTML) and put it before each of the items that 
   * the QueryPath object currently contains. Contrast this with after().
   * 
   * @param mixed $data
   *  The data to be inserted. This can be XML in a string, a DomFragment, a DOMElement,
   *  or the other usual suspects. (See {@link qp()}).
   * @return QueryPath
   *  Returns the QueryPath with the new modifications. The list of elements currently
   *  selected will remain the same.
   * @see insertBefore()
   * @see after()
   * @see append()
   * @see prepend()
   * @throws QueryPathException
   *  Thrown if $data is an unsupported object type.
   */
  public function before($data) {
    $data = $this->prepareInsert($data);
    foreach ($this->matches as $m) {
      $ins = $data->cloneNode(TRUE);
      $m->parentNode->insertBefore($ins, $m);
    }
    
    return $this;
  }
  /**
   * Insert the current elements into the destination document.
   * The items are inserted before each element in the given QueryPath document.
   * That is, they will be siblings with the current elements.
   *
   * @param QueryPath $dest
   *  Destination QueryPath document.
   * @return QueryPath
   *  The current QueryPath object, unaltered. Only the destination QueryPath
   *  object is altered.
   * @see before()
   * @see insertAfter()
   * @see appendTo()
   * @throws QueryPathException
   *  Thrown if $data is an unsupported object type.
   */
  public function insertBefore(QueryPath $dest) {
    foreach ($this->matches as $m) $dest->before($m);
    return $this;
  }
  /**
   * Insert the contents of the current QueryPath after the nodes in the 
   * destination QueryPath object.
   *
   * @param QueryPath $dest
   *  Destination object where the current elements will be deposited.
   * @return QueryPath
   *  The present QueryPath, unaltered. Only the destination object is altered.
   * @see after()
   * @see insertBefore()
   * @see append()
   * @throws QueryPathException
   *  Thrown if $data is an unsupported object type.
   */
  public function insertAfter(QueryPath $dest) {
    foreach ($this->matches as $m) $dest->after($m);
    return $this;
  }
  /**
   * Insert the given data after each element in the current QueryPath object.
   *
   * This inserts the element as a peer to the currently matched elements.
   * Contrast this with {@link append()}, which inserts the data as children
   * of matched elements.
   *
   * @param mixed $data
   *  The data to be appended.
   * @return QueryPath
   *  The QueryPath object (with the items inserted).
   * @see before()
   * @see append()
   * @throws QueryPathException
   *  Thrown if $data is an unsupported object type.
   */
  public function after($data) {
    $data = $this->prepareInsert($data);
    foreach ($this->matches as $m) {
      $ins = $data->cloneNode(TRUE);
      if (isset($m->nextSibling)) 
        $m->parentNode->insertBefore($ins, $m->nextSibling);
      else
        $m->parentNode->appendChild($ins);
    }
    return $this;
  }
  /**
   * Replace the existing element(s) in the list with a new one.
   *
   * @param mixed $new
   *  A DOMElement or XML in a string. This will replace all elements
   *  currently wrapped in the QueryPath object.
   * @return QueryPath
   *  The QueryPath object wrapping <b>the items that were removed</b>.
   *  This remains consistent with the jQuery API.
   * @see append()
   * @see prepend()
   * @see before()
   * @see after()
   * @see remove()
   * @see replaceAll()
   */
  public function replaceWith($new) {
    $data = $this->prepareInsert($new);
    $found = new SplObjectStorage();
    foreach ($this->matches as $m) {
      $parent = $m->parentNode;
      $parent->insertBefore($data->cloneNode(TRUE), $m);
      $found->attach($parent->removeChild($m));
    }
    $this->setMatches($found);
    return $this;
  }
  /**
   * Remove the parent element from the selected node or nodes.
   *
   * This takes the given list of nodes and "unwraps" them, moving them out of their parent
   * node, and then deleting the parent node.
   *
   * For example, consider this:
   *
   * @code
   *   <root><wrapper><content/></wrapper></root>
   * @endcode
   * 
   * Now we can run this code:
   * @code
   *   qp($xml, 'content')->unwrap();
   * @endcode
   *
   * This will result in:
   *
   * @code
   *   <root><content/></root>
   * @endcode
   * This is the opposite of {@link wrap()}.
   *
   * <b>The root element cannot be unwrapped.</b> It has no parents.
   * If you attempt to use unwrap on a root element, this will throw a QueryPathException.
   * (You can, however, "Unwrap" a child that is a direct descendant of the root element. This
   * will remove the root element, and replace the child as the root element. Be careful, though.
   * You cannot set more than one child as a root element.)
   *
   * @return QueryPath
   *  The QueryPath object, with the same element(s) selected.
   * @throws QueryPathException
   *  An exception is thrown if one attempts to unwrap a root element.
   * @see wrap()
   * @since 2.1
   * @author mbutcher
   */
  public function unwrap() {
    
    // We do this in two loops in order to
    // capture the case where two matches are
    // under the same parent. Othwerwise we might
    // remove a match before we can move it.
    $parents = new SplObjectStorage();
    foreach ($this->matches as $m) {
      
      // Cannot unwrap the root element.
      if ($m->isSameNode($m->ownerDocument->documentElement)) {
        throw new QueryPathException('Cannot unwrap the root element.');
      }
      
      // Move children to peer of parent.
      $parent = $m->parentNode;
      $old = $parent->removeChild($m);
      $parent->parentNode->insertBefore($old, $parent);
      $parents->attach($parent);
    }
    
    // Now that all the children are moved, we 
    // remove all of the parents.
    foreach ($parents as $ele) {
      $ele->parentNode->removeChild($ele);
    }
    
    return $this;
  }
  /**
   * Wrap each element inside of the given markup.
   *
   * Markup is usually a string, but it can also be a DOMNode, a document
   * fragment, a SimpleXMLElement, or another QueryPath object (in which case
   * the first item in the list will be used.)
   *
   * @param mixed $markup 
   *  Markup that will wrap each element in the current list.
   * @return QueryPath
   *  The QueryPath object with the wrapping changes made.
   * @see wrapAll()
   * @see wrapInner()
   */
  public function wrap($markup) {
    $data = $this->prepareInsert($markup);
    
    // If the markup passed in is empty, we don't do any wrapping.
    if (empty($data)) {
      return $this;
    }
    
    foreach ($this->matches as $m) {
      $copy = $data->firstChild->cloneNode(TRUE);
      
      // XXX: Should be able to avoid doing this over and over.
      if ($copy->hasChildNodes()) {
        $deepest = $this->deepestNode($copy); 
        // FIXME: Does this need a different data structure?
        $bottom = $deepest[0];
      }
      else
        $bottom = $copy;

      $parent = $m->parentNode;
      $parent->insertBefore($copy, $m);
      $m = $parent->removeChild($m);
      $bottom->appendChild($m);
      //$parent->appendChild($copy);
    }
    return $this;  
  }
  /**
   * Wrap all elements inside of the given markup.
   *
   * So all elements will be grouped together under this single marked up 
   * item. This works by first determining the parent element of the first item
   * in the list. It then moves all of the matching elements under the wrapper
   * and inserts the wrapper where that first element was found. (This is in 
   * accordance with the way jQuery works.)
   *
   * Markup is usually XML in a string, but it can also be a DOMNode, a document
   * fragment, a SimpleXMLElement, or another QueryPath object (in which case
   * the first item in the list will be used.)
   * 
   * @param string $markup 
   *  Markup that will wrap all elements in the current list.
   * @return QueryPath
   *  The QueryPath object with the wrapping changes made.
   * @see wrap()
   * @see wrapInner()
   */
  public function wrapAll($markup) {
    if ($this->matches->count() == 0) return;
    
    $data = $this->prepareInsert($markup);
    
    if (empty($data)) {
      return $this;
    }
    
    if ($data->hasChildNodes()) {
      $deepest = $this->deepestNode($data); 
      // FIXME: Does this need fixing?
      $bottom = $deepest[0];
    }
    else
      $bottom = $data;

    $first = $this->getFirstMatch();
    $parent = $first->parentNode;
    $parent->insertBefore($data, $first);
    foreach ($this->matches as $m) {
      $bottom->appendChild($m->parentNode->removeChild($m));
    }
    return $this;
  }
  /**
   * Wrap the child elements of each item in the list with the given markup.
   *
   * Markup is usually a string, but it can also be a DOMNode, a document
   * fragment, a SimpleXMLElement, or another QueryPath object (in which case
   * the first item in the list will be used.)
   *
   * @param string $markup 
   *  Markup that will wrap children of each element in the current list.
   * @return QueryPath
   *  The QueryPath object with the wrapping changes made.
   * @see wrap()
   * @see wrapAll()
   */
  public function wrapInner($markup) {
    $data = $this->prepareInsert($markup);
    
    // No data? Short circuit.
    if (empty($data)) return $this;
    
    if ($data->hasChildNodes()) {
      $deepest = $this->deepestNode($data); 
      // FIXME: ???
      $bottom = $deepest[0];
    }
    else
      $bottom = $data;
      
    foreach ($this->matches as $m) {
      if ($m->hasChildNodes()) {
        while($m->firstChild) {
          $kid = $m->removeChild($m->firstChild);
          $bottom->appendChild($kid);
        }
      }
      $m->appendChild($data);
    }
    return $this; 
  }
  /**
   * Reduce the set of matches to the deepest child node in the tree.
   *
   * This loops through the matches and looks for the deepest child node of all of 
   * the matches. "Deepest", here, is relative to the nodes in the list. It is 
   * calculated as the distance from the starting node to the most distant child
   * node. In other words, it is not necessarily the farthest node from the root
   * element, but the farthest note from the matched element.
   *
   * In the case where there are multiple nodes at the same depth, all of the 
   * nodes at that depth will be included.
   *
   * @return QueryPath
   *  The QueryPath wrapping the single deepest node.
   */
  public function deepest() {
    $deepest = 0;
    $winner = new SplObjectStorage();
    foreach ($this->matches as $m) {
      $local_deepest = 0;
      $local_ele = $this->deepestNode($m, 0, NULL, $local_deepest);
      
      // Replace with the new deepest.
      if ($local_deepest > $deepest) {
        $winner = new SplObjectStorage();
        foreach ($local_ele as $lele) $winner->attach($lele);
        $deepest = $local_deepest;
      }
      // Augument with other equally deep elements.
      elseif ($local_deepest == $deepest) {
        foreach ($local_ele as $lele)
          $winner->attach($lele);
      }
    }
    $this->setMatches($winner);
    return $this;
  }
  
  /**
   * A depth-checking function. Typically, it only needs to be
   * invoked with the first parameter. The rest are used for recursion.
   * @see deepest();
   * @param DOMNode $ele
   *  The element.
   * @param int $depth
   *  The depth guage
   * @param mixed $current
   *  The current set.
   * @param DOMNode $deepest
   *  A reference to the current deepest node.
   * @return array
   *  Returns an array of DOM nodes.
   */
  protected function deepestNode(DOMNode $ele, $depth = 0, $current = NULL, &$deepest = NULL) {
    // FIXME: Should this use SplObjectStorage?
    if (!isset($current)) $current = array($ele);
    if (!isset($deepest)) $deepest = $depth;
    if ($ele->hasChildNodes()) {
      foreach ($ele->childNodes as $child) {
        if ($child->nodeType === XML_ELEMENT_NODE) {
          $current = $this->deepestNode($child, $depth + 1, $current, $deepest);
        }
      }
    }
    elseif ($depth > $deepest) {
      $current = array($ele);
      $deepest = $depth;
    }
    elseif ($depth === $deepest) {
      $current[] = $ele;
    }
    return $current;
  }
  
  /**
   * Prepare an item for insertion into a DOM.
   *
   * This handles a variety of boilerplate tasks that need doing before an 
   * indeterminate object can be inserted into a DOM tree.
   * - If item is a string, this is converted into a document fragment and returned.
   * - If item is a QueryPath, then the first item is retrieved and this call function
   *   is called recursivel.
   * - If the item is a DOMNode, it is imported into the current DOM if necessary.
   * - If the item is a SimpleXMLElement, it is converted into a DOM node and then
   *   imported.
   *
   * @param mixed $item
   *  Item to prepare for insert.
   * @return mixed
   *  Returns the prepared item.
   * @throws QueryPathException
   *  Thrown if the object passed in is not of a supprted object type.
   */
  protected function prepareInsert($item) {
    if(empty($item)) {
      return;
    }
    elseif (is_string($item)) {
      // If configured to do so, replace all entities.
      if ($this->options['replace_entities']) {
        $item = QueryPathEntities::replaceAllEntities($item);
      }
      
      $frag = $this->document->createDocumentFragment();
      try {
        set_error_handler(array('QueryPathParseException', 'initializeFromError'), $this->errTypes);
        $frag->appendXML($item);
      }
      // Simulate a finally block.
      catch (Exception $e) {
        restore_error_handler();
        throw $e;
      }
      restore_error_handler();
      return $frag;
    }
    elseif ($item instanceof QueryPath) {
      if ($item->size() == 0) 
        return;
        
      return $this->prepareInsert($item->get(0));
    }
    elseif ($item instanceof DOMNode) {
      if ($item->ownerDocument !== $this->document) {
        // Deep clone this and attach it to this document
        $item = $this->document->importNode($item, TRUE);
      }
      return $item;
    }
    elseif ($item instanceof SimpleXMLElement) {
      $element = dom_import_simplexml($item);
      return $this->document->importNode($element, TRUE);
    }
    // What should we do here?
    //var_dump($item);
    throw new QueryPathException("Cannot prepare item of unsupported type: " . gettype($item));
  }
  /**
   * The tag name of the first element in the list.
   *
   * This returns the tag name of the first element in the list of matches. If
   * the list is empty, an empty string will be used.
   *
   * @see replaceAll()
   * @see replaceWith()
   * @return string
   *  The tag name of the first element in the list.
   */
  public function tag() {
    return ($this->size() > 0) ? $this->getFirstMatch()->tagName : '';
  }
  /**
   * Remove any items from the list if they match the selector.
   *
   * In other words, each item that matches the selector will be remove 
   * from the DOM document. The returned QueryPath wraps the list of 
   * removed elements.
   *
   * If no selector is specified, this will remove all current matches from
   * the document.
   *
   * @param string $selector
   *  A CSS Selector.
   * @return QueryPath
   *  The Query path wrapping a list of removed items.
   * @see replaceAll()
   * @see replaceWith()
   * @see removeChildren()
   */
  public function remove($selector = NULL) {
    if(!empty($selector)) {
      // Do a non-destructive find.
      $query = new QueryPathCssEventHandler($this->matches);
      $query->find($selector);
      $matches = $query->getMatches();
    }
    else {
      $matches = $this->matches;
    }

    $found = new SplObjectStorage();
    foreach ($matches as $item) { 
      // The item returned is (according to docs) different from 
      // the one passed in, so we have to re-store it.
      $found->attach($item->parentNode->removeChild($item));
    }
    
    // Return a clone QueryPath with just the removed items. If
    // no items are found, this will return an empty QueryPath.
    return count($found) == 0 ? new QueryPath() : new QueryPath($found);
  }
  /**
   * This replaces everything that matches the selector with the first value
   * in the current list.
   *
   * This is the reverse of replaceWith.
   *
   * Unlike jQuery, QueryPath cannot assume a default document. Consequently,
   * you must specify the intended destination document. If it is omitted, the
   * present document is assumed to be tthe document. However, that can result
   * in undefined behavior if the selector and the replacement are not sufficiently
   * distinct.
   *
   * @param string $selector
   *  The selector.
   * @param DOMDocument $document
   *  The destination document.
   * @return QueryPath
   *  The QueryPath wrapping the modified document.
   * @deprecated Due to the fact that this is not a particularly friendly method,
   *  and that it can be easily replicated using {@see replaceWith()}, it is to be 
   *  considered deprecated.
   * @see remove()
   * @see replaceWith()
   */
  public function replaceAll($selector, DOMDocument $document) {
    $replacement = $this->size() > 0 ? $this->getFirstMatch() : $this->document->createTextNode('');
    
    $c = new QueryPathCssEventHandler($document);
    $c->find($selector);
    $temp = $c->getMatches();
    foreach ($temp as $item) {
      $node = $replacement->cloneNode();
      $node = $document->importNode($node);
      $item->parentNode->replaceChild($node, $item);
    }
    return qp($document, NULL, $this->options);
  }
  /**
   * Add more elements to the current set of matches.
   *
   * This begins the new query at the top of the DOM again. The results found
   * when running this selector are then merged into the existing results. In
   * this way, you can add additional elements to the existing set.
   *
   * @param string $selector
   *  A valid selector.
   * @return QueryPath
   *  The QueryPath object with the newly added elements.
   * @see append()
   * @see after()
   * @see andSelf()
   * @see end()
   */
  public function add($selector) {
    
    // This is destructive, so we need to set $last:
    $this->last = $this->matches;
    
    foreach (qp($this->document, $selector, $this->options)->get() as $item)
      $this->matches->attach($item);
    return $this;
  }
  /**
   * Revert to the previous set of matches.
   *
   * This will revert back to the last set of matches (before the last 
   * "destructive" set of operations). This undoes any change made to the set of
   * matched objects. Functions like find() and filter() change the 
   * list of matched objects. The end() function will revert back to the last set of
   * matched items.
   *
   * Note that functions that modify the document, but do not change the list of 
   * matched objects, are not "destructive". Thus, calling append('something')->end()
   * will not undo the append() call.
   *
   * Only one level of changes is stored. Reverting beyond that will result in 
   * an empty set of matches. Example:
   *
   * @code
   * // The line below returns the same thing as qp(document, 'p');
   * qp(document, 'p')->find('div')->end();
   * // This returns an empty array:
   * qp(document, 'p')->end();
   * // This returns an empty array:
   * qp(document, 'p')->find('div')->find('span')->end()->end();
   * @endcode
   *
   * The last one returns an empty array because only one level of changes is stored.
   *
   * @return QueryPath
   *  A QueryPath object reflecting the list of matches prior to the last destructive
   *  operation.
   * @see andSelf()
   * @see add()
   */
  public function end() {
    // Note that this does not use setMatches because it must set the previous
    // set of matches to empty array.
    $this->matches = $this->last;
    $this->last = new SplObjectStorage();
    return $this;
  }
  /**
   * Combine the current and previous set of matched objects.
   *
   * Example:
   *
   * @code
   * qp(document, 'p')->find('div')->andSelf();
   * @endcode
   *
   * The code above will contain a list of all p elements and all div elements that 
   * are beneath p elements.
   *
   * @see end();
   * @return QueryPath
   *  A QueryPath object with the results of the last two "destructive" operations.
   * @see add()
   * @see end()
   */
  public function andSelf() {
    // This is destructive, so we need to set $last:
    $last = $this->matches;
    
    foreach ($this->last as $item) $this->matches->attach($item);
    
    $this->last = $last;
    return $this;
  }
  /**
   * Remove all child nodes.
   *
   * This is equivalent to jQuery's empty() function. (However, empty() is a 
   * PHP built-in, and cannot be used as a method name.)
   *
   * @return QueryPath
   *  The QueryPath object with the child nodes removed.
   * @see replaceWith()
   * @see replaceAll()
   * @see remove()
   */
  public function removeChildren() {
    foreach ($this->matches as $m) {
      while($kid = $m->firstChild) {
        $m->removeChild($kid);
      }
    }
    return $this;
  }
  /**
   * Get the children of the elements in the QueryPath object.
   *
   * If a selector is provided, the list of children will be filtered through
   * the selector.
   *
   * @param string $selector
   *  A valid selector.
   * @return QueryPath
   *  A QueryPath wrapping all of the children.
   * @see removeChildren()
   * @see parent()
   * @see parents()
   * @see next()
   * @see prev()
   */
  public function children($selector = NULL) {
    $found = new SplObjectStorage();
    foreach ($this->matches as $m) {
      foreach($m->childNodes as $c) {
        if ($c->nodeType == XML_ELEMENT_NODE) $found->attach($c);
      }
    }
    if (empty($selector)) {
      $this->setMatches($found);
    }
    else {
      $this->matches = $found; // Don't buffer this. It is temporary.
      $this->filter($selector);
    }
    return $this;
  }
  /**
   * Get all child nodes (not just elements) of all items in the matched set.
   *
   * It gets only the immediate children, not all nodes in the subtree.
   *
   * This does not process iframes. Xinclude processing is dependent on the 
   * DOM implementation and configuration.
   *
   * @return QueryPath
   *  A QueryPath object wrapping all child nodes for all elements in the 
   *  QueryPath object.
   * @see find()
   * @see text()
   * @see html()
   * @see innerHTML()
   * @see xml()
   * @see innerXML()
   */
  public function contents() {
    $found = new SplObjectStorage();
    foreach ($this->matches as $m) {
      if (empty($m->childNodes)) continue; // Issue #51
      foreach ($m->childNodes as $c) {
        $found->attach($c);
      }
    }
    $this->setMatches($found);
    return $this;
  }
  /**
   * Get a list of siblings for elements currently wrapped by this object.
   *
   * This will compile a list of every sibling of every element in the 
   * current list of elements. 
   *
   * Note that if two siblings are present in the QueryPath object to begin with,
   * then both will be returned in the matched set, since they are siblings of each 
   * other. In other words,if the matches contain a and b, and a and b are siblings of 
   * each other, than running siblings will return a set that contains 
   * both a and b.
   *
   * @param string $selector
   *  If the optional selector is provided, siblings will be filtered through
   *  this expression.
   * @return QueryPath
   *  The QueryPath containing the matched siblings.
   * @see contents()
   * @see children()
   * @see parent()
   * @see parents()
   */
  public function siblings($selector = NULL) {
    $found = new SplObjectStorage();
    foreach ($this->matches as $m) {
      $parent = $m->parentNode;
      foreach ($parent->childNodes as $n) {
        if ($n->nodeType == XML_ELEMENT_NODE && $n !== $m) {
          $found->attach($n);
        }
      }
    }
    if (empty($selector)) {
      $this->setMatches($found);
    }
    else {
      $this->matches = $found; // Don't buffer this. It is temporary.
      $this->filter($selector);
    }
    return $this;
  }
  /**
   * Find the closest element matching the selector.
   *
   * This finds the closest match in the ancestry chain. It first checks the 
   * present element. If the present element does not match, this traverses up 
   * the ancestry chain (e.g. checks each parent) looking for an item that matches.
   *
   * It is provided for jQuery 1.3 compatibility.
   * @param string $selector
   *  A CSS Selector to match.
   * @return QueryPath
   *  The set of matches.
   * @since 2.0
   */
  public function closest($selector) {
    $found = new SplObjectStorage();
    foreach ($this->matches as $m) {
      
      if (qp($m, NULL, $this->options)->is($selector) > 0) {
        $found->attach($m);
      }
      else {
        while ($m->parentNode->nodeType !== XML_DOCUMENT_NODE) {
          $m = $m->parentNode;
          // Is there any case where parent node is not an element?
          if ($m->nodeType === XML_ELEMENT_NODE && qp($m, NULL, $this->options)->is($selector) > 0) {
            $found->attach($m);
            break;
          }
        }
      }
      
    }
    $this->setMatches($found);
    return $this;
  }
  /**
   * Get the immediate parent of each element in the QueryPath.
   *
   * If a selector is passed, this will return the nearest matching parent for
   * each element in the QueryPath.
   *
   * @param string $selector
   *  A valid CSS3 selector.
   * @return QueryPath
   *  A QueryPath object wrapping the matching parents.
   * @see children()
   * @see siblings()
   * @see parents()
   */
  public function parent($selector = NULL) {
    $found = new SplObjectStorage();
    foreach ($this->matches as $m) {
      while ($m->parentNode->nodeType !== XML_DOCUMENT_NODE) {
        $m = $m->parentNode;
        // Is there any case where parent node is not an element?
        if ($m->nodeType === XML_ELEMENT_NODE) {
          if (!empty($selector)) {
            if (qp($m, NULL, $this->options)->is($selector) > 0) {
              $found->attach($m);
              break;
            }
          }
          else {
            $found->attach($m);
            break;
          }
        }
      }
    }
    $this->setMatches($found);
    return $this;
  }
  /**
   * Get all ancestors of each element in the QueryPath.
   * 
   * If a selector is present, only matching ancestors will be retrieved.
   *
   * @see parent()
   * @param string $selector
   *  A valid CSS 3 Selector.
   * @return QueryPath
   *  A QueryPath object containing the matching ancestors.
   * @see siblings()
   * @see children()
   */
  public function parents($selector = NULL) {
    $found = new SplObjectStorage();
    foreach ($this->matches as $m) {
      while ($m->parentNode->nodeType !== XML_DOCUMENT_NODE) {
        $m = $m->parentNode;
        // Is there any case where parent node is not an element?
        if ($m->nodeType === XML_ELEMENT_NODE) {
          if (!empty($selector)) {
            if (qp($m, NULL, $this->options)->is($selector) > 0)
              $found->attach($m);
          }
          else 
            $found->attach($m);
        }
      }
    }
    $this->setMatches($found);
    return $this;
  }
  /**
   * Set or get the markup for an element.
   * 
   * If $markup is set, then the giving markup will be injected into each
   * item in the set. All other children of that node will be deleted, and this
   * new code will be the only child or children. The markup MUST BE WELL FORMED.
   *
   * If no markup is given, this will return a string representing the child 
   * markup of the first node.
   *
   * <b>Important:</b> This differs from jQuery's html() function. This function
   * returns <i>the current node</i> and all of its children. jQuery returns only
   * the children. This means you do not need to do things like this: 
   * @code$qp->parent()->html()@endcode.
   *
   * By default, this is HTML 4.01, not XHTML. Use {@link xml()} for XHTML.
   *
   * @param string $markup
   *  The text to insert.
   * @return mixed
   *  A string if no markup was passed, or a QueryPath if markup was passed.
   * @see xml()
   * @see text()
   * @see contents()
   */
  public function html($markup = NULL) {
    if (isset($markup)) {
      
      if ($this->options['replace_entities']) {
        $markup = QueryPathEntities::replaceAllEntities($markup);
      }
      
      // Parse the HTML and insert it into the DOM
      //$doc = DOMDocument::loadHTML($markup);
      $doc = $this->document->createDocumentFragment();
      $doc->appendXML($markup);
      $this->removeChildren();
      $this->append($doc);
      return $this;
    }
    $length = $this->size();
    if ($length == 0) {
      return NULL;
    }
    // Only return the first item -- that's what JQ does.
    $first = $this->getFirstMatch();

    // Catch cases where first item is not a legit DOM object.
    if (!($first instanceof DOMNode)) {
      return NULL;
    }
    
    // Added by eabrand.
    if(!$first->ownerDocument->documentElement) {
      return NULL;
    }
    
    if ($first instanceof DOMDocument || $first->isSameNode($first->ownerDocument->documentElement)) {
      return $this->document->saveHTML();
    }
    // saveHTML cannot take a node and serialize it.
    return $this->document->saveXML($first);
  }
  
  /**
   * Fetch the HTML contents INSIDE of the first QueryPath item.
   *
   * <b>This behaves the way jQuery's @codehtml()@endcode function behaves.</b>
   *
   * This gets all children of the first match in QueryPath. 
   *
   * Consider this fragment:
   * @code
   * <div>
   * test <p>foo</p> test
   * </div>
   * @endcode
   *
   * We can retrieve just the contents of this code by doing something like
   * this:
   * @code
   * qp($xml, 'div')->innerHTML();
   * @endcode
   *
   * This would return the following:
   * @codetest <p>foo</p> test@endcode
   *
   * @return string
   *  Returns a string representation of the child nodes of the first
   *  matched element.
   * @see html()
   * @see innerXML()
   * @see innerXHTML()
   * @since 2.0
   */
  public function innerHTML() {
    return $this->innerXML();
  } 
  
  /**
   * Fetch child (inner) nodes of the first match.
   *
   * This will return the children of the present match. For an example, 
   * see {@link innerHTML()}.
   *
   * @see innerHTML()
   * @see innerXML()
   * @return string
   *  Returns a string of XHTML that represents the children of the present
   *  node.
   * @since 2.0
   */
  public function innerXHTML() {
    $length = $this->size();
    if ($length == 0) {
      return NULL;
    }
    // Only return the first item -- that's what JQ does.
    $first = $this->getFirstMatch();

    // Catch cases where first item is not a legit DOM object.
    if (!($first instanceof DOMNode)) {
      return NULL;
    }
    elseif (!$first->hasChildNodes()) {
      return '';
    }
    
    $buffer = '';
    foreach ($first->childNodes as $child) {
      $buffer .= $this->document->saveXML($child, LIBXML_NOEMPTYTAG);
    }
    
    return $buffer;
  }
  
  /**
   * Fetch child (inner) nodes of the first match.
   *
   * This will return the children of the present match. For an example, 
   * see {@link innerHTML()}.
   *
   * @see innerHTML()
   * @see innerXHTML()
   * @return string
   *  Returns a string of XHTML that represents the children of the present
   *  node.
   * @since 2.0
   */
  public function innerXML() {
    $length = $this->size();
    if ($length == 0) {
      return NULL;
    }
    // Only return the first item -- that's what JQ does.
    $first = $this->getFirstMatch();

    // Catch cases where first item is not a legit DOM object.
    if (!($first instanceof DOMNode)) {
      return NULL;
    }
    elseif (!$first->hasChildNodes()) {
      return '';
    }
    
    $buffer = '';
    foreach ($first->childNodes as $child) {
      $buffer .= $this->document->saveXML($child);
    }
    
    return $buffer;
  }
  
  /**
   * Retrieve the text of each match and concatenate them with the given separator.
   *
   * This has the effect of looping through all children, retrieving their text
   * content, and then concatenating the text with a separator.
   *
   * @param string $sep
   *  The string used to separate text items. The default is a comma followed by a
   *  space.
   * @param boolean $filterEmpties
   *  If this is true, empty items will be ignored.
   * @return string
   *  The text contents, concatenated together with the given separator between
   *  every pair of items.
   * @see implode()
   * @see text()
   * @since 2.0
   */
  public function textImplode($sep = ', ', $filterEmpties = TRUE) {
    $tmp = array(); 
    foreach ($this->matches as $m) {
      $txt = $m->textContent;
      $trimmed = trim($txt);
      // If filter empties out, then we only add items that have content.
      if ($filterEmpties) {
        if (strlen($trimmed) > 0) $tmp[] = $txt;
      }
      // Else add all content, even if it's empty.
      else {
        $tmp[] = $txt;
      }
    }
    return implode($sep, $tmp);
  }
  /**
   * Get the text contents from just child elements.
   *
   * This is a specialized variant of textImplode() that implodes text for just the
   * child elements of the current element.
   *
   * @param string $separator
   *  The separator that will be inserted between found text content.
   * @return string
   *  The concatenated values of all children.
   */
  function childrenText($separator = ' ') {
    // Branch makes it non-destructive.
    return $this->branch()->xpath('descendant::text()')->textImplode($separator);
  }
  /**
   * Get or set the text contents of a node.
   * @param string $text
   *  If this is not NULL, this value will be set as the text of the node. It
   *  will replace any existing content.
   * @return mixed 
   *  A QueryPath if $text is set, or the text content if no text
   *  is passed in as a pram.
   * @see html()
   * @see xml()
   * @see contents()
   */
  public function text($text = NULL) {
    if (isset($text)) {
      $this->removeChildren();
      $textNode = $this->document->createTextNode($text);
      foreach ($this->matches as $m) $m->appendChild($textNode);
      return $this;
    }
    // Returns all text as one string:
    $buf = '';
    foreach ($this->matches as $m) $buf .= $m->textContent;
    return $buf;
  }
  /**
   * Get or set the text before each selected item.
   *
   * If $text is passed in, the text is inserted before each currently selected item.
   *
   * If no text is given, this will return the concatenated text after each selected element.
   *
   * @code
   * <?php
   * $xml = '<?xml version="1.0"?><root>Foo<a>Bar</a><b/></root>';
   * 
   * // This will return 'Foo'
   * qp($xml, 'a')->textBefore();
   *
   * // This will insert 'Baz' right before <b/>.
   * qp($xml, 'b')->textBefore('Baz');
   * ?>
   * @endcode
   *
   * @param string $text
   *  If this is set, it will be inserted before each node in the current set of
   *  selected items.
   * @return mixed
   *  Returns the QueryPath object if $text was set, and returns a string (possibly empty)
   *  if no param is passed.
   */
  public function textBefore($text = NULL) {
    if (isset($text)) {
      $textNode = $this->document->createTextNode($text);
      return $this->before($textNode);
    }
    $buffer = '';
    foreach ($this->matches as $m) {
      $p = $m;
      while (isset($p->previousSibling) && $p->previousSibling->nodeType == XML_TEXT_NODE) {
        $p = $p->previousSibling;
        $buffer .= $p->textContent;
      }
    }
    return $buffer;
  }
  
  public function textAfter($text = NULL) {
    if (isset($text)) {
      $textNode = $this->document->createTextNode($text);
      return $this->after($textNode);
    }
    $buffer = '';
    foreach ($this->matches as $m) {
      $n = $m;
      while (isset($n->nextSibling) && $n->nextSibling->nodeType == XML_TEXT_NODE) {
        $n = $n->nextSibling;
        $buffer .= $n->textContent;
      }
    }
    return $buffer;
  }
  
  /**
   * Set or get the value of an element's 'value' attribute.
   *
   * The 'value' attribute is common in HTML form elements. This is a 
   * convenience function for accessing the values. Since this is not  common
   * task on the server side, this method may be removed in future releases. (It 
   * is currently provided for jQuery compatibility.)
   *
   * If a value is provided in the params, then the value will be set for all 
   * matches. If no params are given, then the value of the first matched element
   * will be returned. This may be NULL.
   *
   * @deprecated Just use attr(). There's no reason to use this on the server.
   * @see attr()
   * @param string $value
   * @return mixed
   *  Returns a QueryPath if a string was passed in, and a string if no string
   *  was passed in. In the later case, an error will produce NULL.
   */
  public function val($value = NULL) {
    if (isset($value)) {
      $this->attr('value', $value);
      return $this;
    }
    return $this->attr('value');
  }
  /**
   * Set or get XHTML markup for an element or elements.
   * 
   * This differs from {@link html()} in that it processes (and produces)
   * strictly XML 1.0 compliant markup.
   *
   * Like {@link xml()} and {@link html()}, this functions as both a 
   * setter and a getter.
   *
   * This is a convenience function for fetching HTML in XML format.
   * It does no processing of the markup (such as schema validation).
   * @param string $markup
   *  A string containing XML data.
   * @return mixed
   *  If markup is passed in, a QueryPath is returned. If no markup is passed
   *  in, XML representing the first matched element is returned.
   * @see html()
   * @see innerXHTML()
   */
  public function xhtml($markup = NULL) {
    
    // XXX: This is a minor reworking of the original xml() method.
    // This should be refactored, probably.
    // See http://github.com/technosophos/querypath/issues#issue/10
   
    $omit_xml_decl = $this->options['omit_xml_declaration'];
    if ($markup === TRUE) {
      // Basically, we handle the special case where we don't
      // want the XML declaration to be displayed.
      $omit_xml_decl = TRUE;
    }
    elseif (isset($markup)) {
      return $this->xml($markup);
    }
    
    $length = $this->size();
    if ($length == 0) {
      return NULL;
    }
    
    // Only return the first item -- that's what JQ does.
    $first = $this->getFirstMatch();
    // Catch cases where first item is not a legit DOM object.
    if (!($first instanceof DOMNode)) {
      return NULL;
    }
    
    if ($first instanceof DOMDocument || $first->isSameNode($first->ownerDocument->documentElement)) {
      
      // Has the unfortunate side-effect of stripping doctype.
      //$text = ($omit_xml_decl ? $this->document->saveXML($first->ownerDocument->documentElement, LIBXML_NOEMPTYTAG) : $this->document->saveXML(NULL, LIBXML_NOEMPTYTAG));
      $text = $this->document->saveXML(NULL, LIBXML_NOEMPTYTAG);
    }
    else {
      $text = $this->document->saveXML($first, LIBXML_NOEMPTYTAG);
    }
    
    // Issue #47: Using the old trick for removing the XML tag also removed the 
    // doctype. So we remove it with a regex:
    if ($omit_xml_decl) {
      $text = preg_replace('/<\?xml\s[^>]*\?>/', '', $text);
    }
    
    // This is slightly lenient: It allows for cases where code incorrectly places content
    // inside of these supposedly unary elements.
    $unary = '/<(area|base|basefont|br|col|frame|hr|img|input|isindex|link|meta|param)(?(?=\s)([^>\/]+))><\/[^>]*>/i';
    $text = preg_replace($unary, '<\\1\\2 />', $text);
    
    // Experimental: Support for enclosing CDATA sections with comments to be both XML compat
    // and HTML 4/5 compat
    $cdata = '/(<!\[CDATA\[|\]\]>)/i';
    $replace = $this->options['escape_xhtml_js_css_sections'];
    $text = preg_replace($cdata, $replace, $text);
    
    return $text;
  }
  /**
   * Set or get the XML markup for an element or elements.
   *
   * Like {@link html()}, this functions in both a setter and a getter mode.
   * 
   * In setter mode, the string passed in will be parsed and then appended to the 
   * elements wrapped by this QueryPath object.When in setter mode, this parses 
   * the XML using the DOMFragment parser. For that reason, an XML declaration 
   * is not necessary.
   *
   * In getter mode, the first element wrapped by this QueryPath object will be 
   * converted to an XML string and returned.
   *
   * @param string $markup
   *  A string containing XML data.
   * @return mixed
   *  If markup is passed in, a QueryPath is returned. If no markup is passed
   *  in, XML representing the first matched element is returned.
   * @see xhtml()
   * @see html()
   * @see text()
   * @see content()
   * @see innerXML()
   */
  public function xml($markup = NULL) {
    $omit_xml_decl = $this->options['omit_xml_declaration'];
    if ($markup === TRUE) {
      // Basically, we handle the special case where we don't
      // want the XML declaration to be displayed.
      $omit_xml_decl = TRUE;
    }
    elseif (isset($markup)) {
      if ($this->options['replace_entities']) {
        $markup = QueryPathEntities::replaceAllEntities($markup);
      }
      $doc = $this->document->createDocumentFragment();
      $doc->appendXML($markup);
      $this->removeChildren();
      $this->append($doc);
      return $this;
    }
    $length = $this->size();
    if ($length == 0) {
      return NULL;
    }
    // Only return the first item -- that's what JQ does.
    $first = $this->getFirstMatch();
    
    // Catch cases where first item is not a legit DOM object.
    if (!($first instanceof DOMNode)) {
      return NULL;
    }
    
    if ($first instanceof DOMDocument || $first->isSameNode($first->ownerDocument->documentElement)) {
      
      return  ($omit_xml_decl ? $this->document->saveXML($first->ownerDocument->documentElement) : $this->document->saveXML());
    }
    return $this->document->saveXML($first);
  }
  /**
   * Send the XML document to the client.
   * 
   * Write the document to a file path, if given, or 
   * to stdout (usually the client).
   *
   * This prints the entire document.
   *
   * @param string $path
   *  The path to the file into which the XML should be written. if 
   *  this is NULL, data will be written to STDOUT, which is usually
   *  sent to the remote browser.
   * @param int $options
   *  (As of QueryPath 2.1) Pass libxml options to the saving mechanism.
   * @return QueryPath
   *  The QueryPath object, unmodified.
   * @see xml()
   * @see innerXML()
   * @see writeXHTML()
   * @throws Exception 
   *  In the event that a file cannot be written, an Exception will be thrown.
   */
  public function writeXML($path = NULL, $options = NULL) {
    if ($path == NULL) {
      print $this->document->saveXML(NULL, $options);
    }
    else {
      try {
        set_error_handler(array('QueryPathIOException', 'initializeFromError'));
        $this->document->save($path, $options);
      }
      catch (Exception $e) {
        restore_error_handler();
        throw $e;
      }
      restore_error_handler();
    }
    return $this;
  }
  /**
   * Writes HTML to output.
   *
   * HTML is formatted as HTML 4.01, without strict XML unary tags. This is for
   * legacy HTML content. Modern XHTML should be written using {@link toXHTML()}.
   * 
   * Write the document to stdout (usually the client) or to a file.
   *
   * @param string $path
   *  The path to the file into which the XML should be written. if 
   *  this is NULL, data will be written to STDOUT, which is usually
   *  sent to the remote browser.
   * @return QueryPath
   *  The QueryPath object, unmodified.
   * @see html()
   * @see innerHTML()
   * @throws Exception 
   *  In the event that a file cannot be written, an Exception will be thrown.
   */
  public function writeHTML($path = NULL) {
    if ($path == NULL) {
      print $this->document->saveHTML();
    }
    else {
      try {
        set_error_handler(array('QueryPathParseException', 'initializeFromError'));
        $this->document->saveHTMLFile($path);
      }
      catch (Exception $e) {
        restore_error_handler();
        throw $e;
      }
      restore_error_handler();
    }
    return $this;
  }
  
  /**
   * Write an XHTML file to output.
   *
   * Typically, you should use this instead of {@link writeHTML()}.
   *
   * Currently, this functions identically to {@link toXML()} <i>except that</i>
   * it always uses closing tags (e.g. always @code<script></script>@endcode, 
   * never @code<script/>@endcode). It will
   * write the file as well-formed XML. No XHTML schema validation is done.
   *
   * @see writeXML()
   * @see xml()
   * @see writeHTML()
   * @see innerXHTML()
   * @see xhtml()
   * @param string $path
   *  The filename of the file to write to.
   * @return QueryPath
   *  Returns the QueryPath, unmodified.
   * @throws Exception
   *  In the event that the output file cannot be written, an exception is
   *  thrown.
   * @since 2.0
   */
  public function writeXHTML($path = NULL) {
    return $this->writeXML($path, LIBXML_NOEMPTYTAG);
    /*
    if ($path == NULL) {
      print $this->document->saveXML(NULL, LIBXML_NOEMPTYTAG);
    }
    else {
      try {
        set_error_handler(array('QueryPathIOException', 'initializeFromError'));
        $this->document->save($path, LIBXML_NOEMPTYTAG);
      }
      catch (Exception $e) {
        restore_error_handler();
        throw $e;
      }
      restore_error_handler();
    }
    return $this;
    */
  }
  /**
   * Get the next sibling of each element in the QueryPath.
   *
   * If a selector is provided, the next matching sibling will be returned.
   *
   * @param string $selector
   *  A CSS3 selector.
   * @return QueryPath
   *  The QueryPath object.
   * @see nextAll()
   * @see prev()
   * @see children()
   * @see contents()
   * @see parent()
   * @see parents()
   */
  public function next($selector = NULL) {
    $found = new SplObjectStorage();
    foreach ($this->matches as $m) {
      while (isset($m->nextSibling)) {
        $m = $m->nextSibling;
        if ($m->nodeType === XML_ELEMENT_NODE) {
          if (!empty($selector)) {
            if (qp($m, NULL, $this->options)->is($selector) > 0) {
              $found->attach($m);
              break;
            }
          }
          else {
            $found->attach($m);
            break;
          }
        }
      }
    }
    $this->setMatches($found);
    return $this;
  }
  /**
   * Get all siblings after an element.
   *
   * For each element in the QueryPath, get all siblings that appear after
   * it. If a selector is passed in, then only siblings that match the 
   * selector will be included.
   *
   * @param string $selector 
   *  A valid CSS 3 selector.
   * @return QueryPath
   *  The QueryPath object, now containing the matching siblings.
   * @see next()
   * @see prevAll()
   * @see children()
   * @see siblings()
   */
  public function nextAll($selector = NULL) {
    $found = new SplObjectStorage();
    foreach ($this->matches as $m) {
      while (isset($m->nextSibling)) {
        $m = $m->nextSibling;
        if ($m->nodeType === XML_ELEMENT_NODE) {
          if (!empty($selector)) {
            if (qp($m, NULL, $this->options)->is($selector) > 0) {
              $found->attach($m);
            }
          }
          else {
            $found->attach($m);
          }
        }
      }
    }
    $this->setMatches($found);
    return $this;
  }
  /**
   * Get the next sibling before each element in the QueryPath.
   *
   * For each element in the QueryPath, this retrieves the previous sibling
   * (if any). If a selector is supplied, it retrieves the first matching 
   * sibling (if any is found).
   *
   * @param string $selector
   *  A valid CSS 3 selector.
   * @return QueryPath
   *  A QueryPath object, now containing any previous siblings that have been 
   *  found.
   * @see prevAll()
   * @see next()
   * @see siblings()
   * @see children()
   */
  public function prev($selector = NULL) {
    $found = new SplObjectStorage();
    foreach ($this->matches as $m) {
      while (isset($m->previousSibling)) {
        $m = $m->previousSibling;
        if ($m->nodeType === XML_ELEMENT_NODE) {
          if (!empty($selector)) {
            if (qp($m, NULL, $this->options)->is($selector)) {
              $found->attach($m);
              break;
            }
          }
          else {
            $found->attach($m);
            break;
          }
        }
      }
    }
    $this->setMatches($found);
    return $this;
  }
  /**
   * Get the previous siblings for each element in the QueryPath.
   *
   * For each element in the QueryPath, get all previous siblings. If a 
   * selector is provided, only matching siblings will be retrieved.
   *
   * @param string $selector
   *  A valid CSS 3 selector.
   * @return QueryPath
   *  The QueryPath object, now wrapping previous sibling elements.
   * @see prev()
   * @see nextAll()
   * @see siblings()
   * @see contents()
   * @see children()
   */
  public function prevAll($selector = NULL) {
    $found = new SplObjectStorage();
    foreach ($this->matches as $m) {
      while (isset($m->previousSibling)) {
        $m = $m->previousSibling;
        if ($m->nodeType === XML_ELEMENT_NODE) {
          if (!empty($selector)) {
            if (qp($m, NULL, $this->options)->is($selector)) {
              $found->attach($m);
            }
          }
          else {
            $found->attach($m);
          }
        }
      }
    }
    $this->setMatches($found);
    return $this;
  }
  /**
   * @deprecated Use {@link siblings()}.
   */
  public function peers($selector = NULL) {
    $found = new SplObjectStorage();
    foreach ($this->matches as $m) {
      foreach ($m->parentNode->childNodes as $kid) {
        if ($kid->nodeType == XML_ELEMENT_NODE && $m !== $kid) {
          if (!empty($selector)) {
            if (qp($kid, NULL, $this->options)->is($selector)) {
              $found->attach($kid);
            }
          }
          else {
            $found->attach($kid);
          }
        }
      }
    }
    $this->setMatches($found);
    return $this;
  }
  /**
   * Add a class to all elements in the current QueryPath.
   *
   * This searchers for a class attribute on each item wrapped by the current 
   * QueryPath object. If no attribute is found, a new one is added and its value
   * is set to $class. If a class attribute is found, then the value is appended
   * on to the end.
   *
   * @param string $class 
   *  The name of the class.
   * @return QueryPath
   *  Returns the QueryPath object.
   * @see css()
   * @see attr()
   * @see removeClass()
   * @see hasClass()
   */
  public function addClass($class) {
    foreach ($this->matches as $m) {
      if ($m->hasAttribute('class')) {
        $val = $m->getAttribute('class');
        $m->setAttribute('class', $val . ' ' . $class);
      }
      else {
        $m->setAttribute('class', $class);
      }
    }
    return $this;
  }
  /**
   * Remove the named class from any element in the QueryPath that has it.
   *
   * This may result in the entire class attribute being removed. If there
   * are other items in the class attribute, though, they will not be removed.
   * 
   * Example:
   * Consider this XML:
   * @code
   * <element class="first second"/>
   * @endcode
   *
   * Executing this fragment of code will remove only the 'first' class:
   * @code
   * qp(document, 'element')->removeClass('first');
   * @endcode
   *
   * The resulting XML will be:
   * @code
   * <element class="second"/>
   * @endcode
   *
   * To remove the entire 'class' attribute, you should use {@see removeAttr()}.
   *
   * @param string $class
   *  The class name to remove.
   * @return QueryPath
   *  The modified QueryPath object.
   * @see attr()
   * @see addClass()
   * @see hasClass()
   */
  public function removeClass($class) {
    foreach ($this->matches as $m) {
      if ($m->hasAttribute('class')) {
        $vals = explode(' ', $m->getAttribute('class'));
        if (in_array($class, $vals)) {
          $buf = array();
          foreach ($vals as $v) {
            if ($v != $class) $buf[] = $v;
          }
          if (count($buf) == 0)
            $m->removeAttribute('class');
          else
            $m->setAttribute('class', implode(' ', $buf));
        }
      }
    }
    return $this;
  }
  /**
   * Returns TRUE if any of the elements in the QueryPath have the specified class.
   *
   * @param string $class
   *  The name of the class.
   * @return boolean 
   *  TRUE if the class exists in one or more of the elements, FALSE otherwise.
   * @see addClass()
   * @see removeClass()
   */
  public function hasClass($class) {
    foreach ($this->matches as $m) {
      if ($m->hasAttribute('class')) {
        $vals = explode(' ', $m->getAttribute('class'));
        if (in_array($class, $vals)) return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Branch the base QueryPath into another one with the same matches.
   *
   * This function makes a copy of the QueryPath object, but keeps the new copy 
   * (initially) pointed at the same matches. This object can then be queried without
   * changing the original QueryPath. However, changes to the elements inside of this
   * QueryPath will show up in the QueryPath from which it is branched.
   * 
   * Compare this operation with {@link cloneAll()}. The cloneAll() call takes
   * the current QueryPath object and makes a copy of all of its matches. You continue
   * to operate on the same QueryPath object, but the elements inside of the QueryPath
   * are copies of those before the call to cloneAll().
   *
   * This, on the other hand, copies <i>the QueryPath</i>, but keeps valid 
   * references to the document and the wrapped elements. A new query branch is 
   * created, but any changes will be written back to the same document.
   *
   * In practice, this comes in handy when you want to do multiple queries on a part
   * of the document, but then return to a previous set of matches. (see {@link QPTPL}
   * for examples of this in practice).
   *
   * Example:
   *
   * @code
   * <?php
   * $qp = qp(QueryPath::HTML_STUB);
   * $branch = $qp->branch();
   * $branch->find('title')->text('Title');
   * $qp->find('body')->text('This is the body')->writeHTML;
   * ?>
   * @endcode
   *
   * Notice that in the code, each of the QueryPath objects is doing its own 
   * query. However, both are modifying the same document. The result of the above 
   * would look something like this:
   *
   * @code
   * <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
   * <html xmlns="http://www.w3.org/1999/xhtml">
   * <head>
   *    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"></meta>
   *    <title>Title</title>
   * </head>
   * <body>This is the body</body>
   * </html>
   * @endcode
   *
   * Notice that while $qp and $banch were performing separate queries, they 
   * both modified the same document.
   *
   * In jQuery or a browser-based solution, you generally do not need a branching
   * function because there is (implicitly) only one document. In QueryPath, there
   * is no implicit document. Every document must be explicitly specified (and,
   * in most cases, parsed -- which is costly). Branching makes it possible to 
   * work on one document with multiple QueryPath objects.
   *
   * @param string $selector
   *  If a selector is passed in, an additional {@link find()} will be executed
   *  on the branch before it is returned. (Added in QueryPath 2.0.)
   * @return QueryPath
   *  A copy of the QueryPath object that points to the same set of elements that
   *  the original QueryPath was pointing to.
   * @since 1.1
   * @see cloneAll()
   * @see find()
   */
  public function branch($selector = NULL) {
    $temp = qp($this->matches, NULL, $this->options);
    if (isset($selector)) $temp->find($selector);
    return $temp;
  }
  /**
   * Perform a deep clone of each node in the QueryPath.
   *
   * This does not clone the QueryPath object, but instead clones the 
   * list of nodes wrapped by the QueryPath. Every element is deeply 
   * cloned.
   *
   * This method is analogous to jQuery's clone() method.
   *
   * This is a destructive operation, which means that end() will revert
   * the list back to the clone's original.
   * @see qp()
   * @return QueryPath
   */
  public function cloneAll() {
    $found = new SplObjectStorage();
    foreach ($this->matches as $m) $found->attach($m->cloneNode(TRUE));
    $this->setMatches($found, FALSE);
    return $this;
  }
  
  /**
   * Clone the QueryPath.
   *
   * This makes a deep clone of the elements inside of the QueryPath.
   *
   * This clones only the QueryPathImpl, not all of the decorators. The
   * clone operator in PHP should handle the cloning of the decorators.
   */
  public function __clone() {
    //XXX: Should we clone the document?
    
    // Make sure we clone the kids.
    $this->cloneAll();
  }  

  /**
   * Detach any items from the list if they match the selector.
   *
   * In other words, each item that matches the selector will be remove
   * from the DOM document. The returned QueryPath wraps the list of
   * removed elements.
   *
   * If no selector is specified, this will remove all current matches from
   * the document.
   *
   * @param string $selector
   *  A CSS Selector.
   * @return QueryPath
   *  The Query path wrapping a list of removed items.
   * @see replaceAll()
   * @see replaceWith()
   * @see removeChildren()
   * @since 2.1
   * @author eabrand
   */
  public function detach($selector = NULL) {

    if(!empty($selector))
    $this->find($selector);

    $found = new SplObjectStorage();
    $this->last = $this->matches;
    foreach ($this->matches as $item) {
      // The item returned is (according to docs) different from
      // the one passed in, so we have to re-store it.
      $found->attach($item->parentNode->removeChild($item));
    }
    $this->setMatches($found);
    return $this;
  }

  /**
   * Attach any items from the list if they match the selector.
   *
   * If no selector is specified, this will remove all current matches from
   * the document.
   *
   * @param QueryPath $dest
   *  A QueryPath Selector.
   * @return QueryPath
   *  The Query path wrapping a list of removed items.
   * @see replaceAll()
   * @see replaceWith()
   * @see removeChildren()
   * @since 2.1
   * @author eabrand
   */
  public function attach(QueryPath $dest) {
    foreach ($this->last as $m) $dest->append($m);
    return $this;
  }
  
  /**
   * Reduce the elements matched by QueryPath to only those which contain the given item.
   *
   * There are two ways in which this is different from jQuery's implementation:
   * - We allow ANY DOMNode, not just DOMElements. That means this will work on 
   *   processor instructions, text nodes, comments, etc.
   * - Unlike jQuery, this implementation of has() follows QueryPath standard behavior
   *   and modifies the existing object. It does not create a brand new object.
   * 
   * @param mixed $contained
   *   - If $contained is a CSS selector (e.g. '#foo'), this will test to see
   *     if the current QueryPath has any elements that contain items that match
   *     the selector.
   *   - If $contained is a DOMNode, then this will test to see if THE EXACT DOMNode
   *     exists in the currently matched elements. (Note that you cannot match across DOM trees, even if it is the same document.)
   * @since 2.1
   * @author eabrand
   * @todo It would be trivially easy to add support for iterating over an array or Iterable of DOMNodes.
   */
  public function has($contained) {
    $found = new SplObjectStorage();
    
    // If it's a selector, we just get all of the DOMNodes that match the selector.
    $nodes = array();
    if (is_string($contained)) {
      // Get the list of nodes.
      $nodes = $this->branch($contained)->get();
    }
    elseif ($contained instanceof DOMNode) {
      // Make a list with one node.
      $nodes = array($contained);
    }
    
    // Now we go through each of the nodes that we are testing. We want to find
    // ALL PARENTS that are in our existing QueryPath matches. Those are the
    // ones we add to our new matches.
    foreach ($nodes as $original_node) {
      $node = $original_node;
      while (!empty($node)/* && $node != $node->ownerDocument*/) {
        if ($this->matches->contains($node)) {
          $found->attach($node);
        }
        $node = $node->parentNode;
      }
    }
    
    $this->setMatches($found);
    return $this;
  }

  /**
   * Empty everything within the specified element.
   *
   * A convenience function for removeChildren(). This is equivalent to jQuery's 
   * empty() function. However, `empty` is a built-in in PHP, and cannot be used as a 
   * function name.
   *
   * @return QueryPath
   *  The QueryPath object with the newly emptied elements.
   * @see removeChildren()
   * @since 2.1
   * @author eabrand
   * @deprecated The removeChildren() function is the preferred method.
   */
  public function emptyElement() {
    $this->removeChildren();
    return $this;
  }
  
  /**
   * Get the even elements, so counter-intuitively 1, 3, 5, etc.
   *
   *
   *
   * @return QueryPath
   *  A QueryPath wrapping all of the children.
   * @see removeChildren()
   * @see parent()
   * @see parents()
   * @see next()
   * @see prev()
   * @since 2.1
   * @author eabrand
   */
  public function even() {
    $found = new SplObjectStorage();
    $even = false;
    foreach ($this->matches as $m) {
      if ($even && $m->nodeType == XML_ELEMENT_NODE) $found->attach($m);
      $even = ($even) ? false : true;
    }
    $this->setMatches($found);
    $this->matches = $found; // Don't buffer this. It is temporary.
    return $this;
  }

  /**
   * Get the odd elements, so counter-intuitively 0, 2, 4, etc.
   *
   *
   *
   * @return QueryPath
   *  A QueryPath wrapping all of the children.
   * @see removeChildren()
   * @see parent()
   * @see parents()
   * @see next()
   * @see prev()
   * @since 2.1
   * @author eabrand
   */
  public function odd() {
    $found = new SplObjectStorage();
    $odd = true;
    foreach ($this->matches as $m) {
      if ($odd && $m->nodeType == XML_ELEMENT_NODE) $found->attach($m);
      $odd = ($odd) ? false : true;
    }
    $this->setMatches($found);
    $this->matches = $found; // Don't buffer this. It is temporary.
    return $this;
  }
  
  /**
   * Get the first matching element.
   *
   *
   * @return QueryPath
   *  A QueryPath wrapping all of the children.
   * @see next()
   * @see prev()
   * @since 2.1
   * @author eabrand
   */
  public function first() {
    $found = new SplObjectStorage();
    foreach ($this->matches as $m) {
      if ($m->nodeType == XML_ELEMENT_NODE) {
        $found->attach($m);
        break;
      }
    }
    $this->setMatches($found);
    $this->matches = $found; // Don't buffer this. It is temporary.
    return $this;
  }

  /**
   * Get the first child of the matching element.
   *
   *
   * @return QueryPath
   *  A QueryPath wrapping all of the children.
   * @see next()
   * @see prev()
   * @since 2.1
   * @author eabrand
   */
  public function firstChild() {
    // Could possibly use $m->firstChild http://theserverpages.com/php/manual/en/ref.dom.php
    $found = new SplObjectStorage();
    $flag = false;
    foreach ($this->matches as $m) {
      foreach($m->childNodes as $c) {
        if ($c->nodeType == XML_ELEMENT_NODE) {
          $found->attach($c);
          $flag = true;
          break;
        }
      }
      if($flag) break;
    }
    $this->setMatches($found);
    $this->matches = $found; // Don't buffer this. It is temporary.
    return $this;
  }

  /**
   * Get the last matching element.
   *
   *
   * @return QueryPath
   *  A QueryPath wrapping all of the children.
   * @see next()
   * @see prev()
   * @since 2.1
   * @author eabrand
   */
  public function last() {
    $found = new SplObjectStorage();
    $item = null;
    foreach ($this->matches as $m) {
      if ($m->nodeType == XML_ELEMENT_NODE) {
        $item = $m;
      }
    }
    if ($item) {
      $found->attach($item);
    }
    $this->setMatches($found);
    $this->matches = $found; // Don't buffer this. It is temporary.
    return $this;
  }

  /**
   * Get the last child of the matching element.
   *
   *
   * @return QueryPath
   *  A QueryPath wrapping all of the children.
   * @see next()
   * @see prev()
   * @since 2.1
   * @author eabrand
   */
  public function lastChild() {
    $found = new SplObjectStorage();
    $item = null;
    foreach ($this->matches as $m) {
      foreach($m->childNodes as $c) {
        if ($c->nodeType == XML_ELEMENT_NODE) {
          $item = $c;
        }
      }
      if ($item) {
        $found->attach($item);
        $item = null;
      }
    }
    $this->setMatches($found);
    $this->matches = $found; // Don't buffer this. It is temporary.
    return $this;
  }

  /**
   * Get all siblings after an element until the selector is reached.
   *
   * For each element in the QueryPath, get all siblings that appear after
   * it. If a selector is passed in, then only siblings that match the
   * selector will be included.
   *
   * @param string $selector
   *  A valid CSS 3 selector.
   * @return QueryPath
   *  The QueryPath object, now containing the matching siblings.
   * @see next()
   * @see prevAll()
   * @see children()
   * @see siblings()
   * @since 2.1
   * @author eabrand
   */
  public function nextUntil($selector = NULL) {
    $found = new SplObjectStorage();
    foreach ($this->matches as $m) {
      while (isset($m->nextSibling)) {
        $m = $m->nextSibling;
        if ($m->nodeType === XML_ELEMENT_NODE) {
          if (!empty($selector)) {
            if (qp($m, NULL, $this->options)->is($selector) > 0) {
              break;
            }
            else {
              $found->attach($m);
            }
          }
          else {
            $found->attach($m);
          }
        }
      }
    }
    $this->setMatches($found);
    return $this;
  } 

  /**
   * Get the previous siblings for each element in the QueryPath
   * until the selector is reached.
   *
   * For each element in the QueryPath, get all previous siblings. If a
   * selector is provided, only matching siblings will be retrieved.
   *
   * @param string $selector
   *  A valid CSS 3 selector.
   * @return QueryPath
   *  The QueryPath object, now wrapping previous sibling elements.
   * @see prev()
   * @see nextAll()
   * @see siblings()
   * @see contents()
   * @see children()
   * @since 2.1
   * @author eabrand
   */
  public function prevUntil($selector = NULL) {
    $found = new SplObjectStorage();
    foreach ($this->matches as $m) {
      while (isset($m->previousSibling)) {
        $m = $m->previousSibling;
        if ($m->nodeType === XML_ELEMENT_NODE) {
          if (!empty($selector) && qp($m, NULL, $this->options)->is($selector))
          break;
          else
          $found->attach($m);
        }
      }
    }
    $this->setMatches($found);
    return $this;
  }
  
  /**
   * Get all ancestors of each element in the QueryPath until the selector is reached.
   *
   * If a selector is present, only matching ancestors will be retrieved.
   *
   * @see parent()
   * @param string $selector
   *  A valid CSS 3 Selector.
   * @return QueryPath
   *  A QueryPath object containing the matching ancestors.
   * @see siblings()
   * @see children()
   * @since 2.1
   * @author eabrand
   */
  public function parentsUntil($selector = NULL) {
    $found = new SplObjectStorage();
    foreach ($this->matches as $m) {
      while ($m->parentNode->nodeType !== XML_DOCUMENT_NODE) {
        $m = $m->parentNode;
        // Is there any case where parent node is not an element?
        if ($m->nodeType === XML_ELEMENT_NODE) {
          if (!empty($selector)) {
            if (qp($m, NULL, $this->options)->is($selector) > 0)
            break;
            else
            $found->attach($m);
          }
          else
          $found->attach($m);
        }
      }
    }
    $this->setMatches($found);
    return $this;
  }
  
  /////// INTERNAL FUNCTIONS ////////
  
  
  /**
   * Determine whether a given string looks like XML or not.
   *
   * Basically, this scans a portion of the supplied string, checking to see
   * if it has a tag-like structure. It is possible to "confuse" this, which
   * may subsequently result in parse errors, but in the vast majority of 
   * cases, this method serves as a valid inicator of whether or not the 
   * content looks like XML.
   *
   * Things that are intentional excluded:
   * - plain text with no markup.
   * - strings that look like filesystem paths.
   * 
   * Subclasses SHOULD NOT OVERRIDE THIS. Altering it may be altering
   * core assumptions about how things work. Instead, classes should 
   * override the constructor and pass in only one of the parsed types
   * that this class expects.
   */
  protected function isXMLish($string) {
    // Long strings will exhaust the regex engine, so we
    // grab a representative string.
    // $test = substr($string, 0, 255);
    return (strpos($string, '<') !== FALSE && strpos($string, '>') !== FALSE);
    //return preg_match(ML_EXP, $test) > 0;
  }
  
  private function parseXMLString($string, $flags = NULL) {
    
    $document = new DOMDocument('1.0');
    $lead = strtolower(substr($string, 0, 5)); // <?xml
    try {
      set_error_handler(array('QueryPathParseException', 'initializeFromError'), $this->errTypes);
      
      if (isset($this->options['convert_to_encoding'])) {
        // Is there another way to do this?
        
        $from_enc = isset($this->options['convert_from_encoding']) ? $this->options['convert_from_encoding'] : 'auto';
        $to_enc = $this->options['convert_to_encoding'];
        
        if (function_exists('mb_convert_encoding')) {
          $string = mb_convert_encoding($string, $to_enc, $from_enc);
        }
        
      }
      
      // This is to avoid cases where low ascii digits have slipped into HTML.
      // AFAIK, it should not adversly effect UTF-8 documents.
      if (!empty($this->options['strip_low_ascii'])) {
        $string = filter_var($string, FILTER_UNSAFE_RAW, FILTER_FLAG_ENCODE_LOW);
      }
      
      // Allow users to override parser settings.
      if (empty($this->options['use_parser'])) {
        $useParser = '';
      }
      else {
        $useParser = strtolower($this->options['use_parser']);
      }
      
      // If HTML parser is requested, we use it.
      if ($useParser == 'html') {
        $document->loadHTML($string);
      }
      // Parse as XML if it looks like XML, or if XML parser is requested.
      elseif ($lead == '<?xml' || $useParser == 'xml') {
        if ($this->options['replace_entities']) {
          $string = QueryPathEntities::replaceAllEntities($string);
        }
        $document->loadXML($string, $flags);
      }
      // In all other cases, we try the HTML parser.
      else {
        $document->loadHTML($string);
      }
    }
    // Emulate 'finally' behavior.
    catch (Exception $e) {
      restore_error_handler();
      throw $e;
    }
    restore_error_handler();
    
    if (empty($document)) {
      throw new QueryPathParseException('Unknown parser exception.');
    }
    return $document;
  }
  
  /**
   * EXPERT: Be very, very careful using this.
   * A utility function for setting the current set of matches.
   * It makes sure the last matches buffer is set (for end() and andSelf()).
   * @since 2.0
   */
  public function setMatches($matches, $unique = TRUE) {
    // This causes a lot of overhead....
    //if ($unique) $matches = self::unique($matches);
    $this->last = $this->matches;
    
    // Just set current matches.
    if ($matches instanceof SplObjectStorage) {
      $this->matches = $matches;
    }
    // This is likely legacy code that needs conversion.
    elseif (is_array($matches)) {
      trigger_error('Legacy array detected.');
      $tmp = new SplObjectStorage();
      foreach ($matches as $m) $tmp->attach($m);
      $this->matches = $tmp;
    }
    // For non-arrays, try to create a new match set and 
    // add this object.
    else {
      $found = new SplObjectStorage();
      if (isset($matches)) $found->attach($matches);
      $this->matches = $found;
    }
    
    // EXPERIMENTAL: Support for qp()->length.
    $this->length = $this->matches->count();
  }
  
  /**
   * Set the match monitor to empty.
   *
   * This preserves history.
   *
   * @since 2.0
   */
  private function noMatches() {
    $this->setMatches(NULL);
  }
    
  /**
   * A utility function for retriving a match by index.
   *
   * The internal data structure used in QueryPath does not have
   * strong random access support, so we suppliment it with this method.
   */
  private function getNthMatch($index) {
    if ($index > $this->matches->count() || $index < 0) return;
    
    $i = 0;
    foreach ($this->matches as $m) {
      if ($i++ == $index) return $m;
    }
  }
  
  /**
   * Convenience function for getNthMatch(0).
   */
  private function getFirstMatch() {
    $this->matches->rewind();
    return $this->matches->current();
  }
  
  /**
   * Parse just a fragment of XML.
   * This will automatically prepend an <?xml ?> declaration before parsing.
   * @param string $string 
   *   Fragment to parse.
   * @return DOMDocumentFragment 
   *   The parsed document fragment.
   */
   /*
  private function parseXMLFragment($string) {
    $frag = $this->document->createDocumentFragment();
    $frag->appendXML($string);
    return $frag;
  }
  */
  
  /**
   * Parse an XML or HTML file.
   *
   * This attempts to autodetect the type of file, and then parse it.
   *
   * @param string $filename
   *  The file name to parse.
   * @param int $flags
   *  The OR-combined flags accepted by the DOM parser. See the PHP documentation
   *  for DOM or for libxml.
   * @param resource $context
   *  The stream context for the file IO. If this is set, then an alternate 
   *  parsing path is followed: The file is loaded by PHP's stream-aware IO
   *  facilities, read entirely into memory, and then handed off to 
   *  {@link parseXMLString()}. On large files, this can have a performance impact.
   * @throws QueryPathParseException 
   *  Thrown when a file cannot be loaded or parsed.
   */
  private function parseXMLFile($filename, $flags = NULL, $context = NULL) {
    
    // If a context is specified, we basically have to do the reading in 
    // two steps:
    if (!empty($context)) {
      try {
        set_error_handler(array('QueryPathParseException', 'initializeFromError'), $this->errTypes);
        $contents = file_get_contents($filename, FALSE, $context);
        
      }
      // Apparently there is no 'finally' in PHP, so we have to restore the error
      // handler this way:
      catch(Exception $e) {
        restore_error_handler();
        throw $e;
      }
      restore_error_handler();
      
      if ($contents == FALSE) {
        throw new QueryPathParseException(sprintf('Contents of the file %s could not be retrieved.', $filename));
      }
      
      
      /* This is basically unneccessary overhead, as it is not more
       * accurate than the existing method.
      if (isset($md['wrapper_type']) &&  $md['wrapper_type'] == 'http') {
        for ($i = 0; $i < count($md['wrapper_data']); ++$i) {
          if (stripos($md['wrapper_data'][$i], 'content-type:') !== FALSE) {
            $ct = trim(substr($md['wrapper_data'][$i], 12));
            if (stripos('text/html') === 0) {
              $this->parseXMLString($contents, $flags, 'text/html');
            }
            else {
              // We can't account for all of the mime types that have
              // an XML payload, so we set it to XML.
              $this->parseXMLString($contents, $flags, 'text/xml');
            }
            break;
          }
        }
      }
      */
      
      return $this->parseXMLString($contents, $flags);
    }
    
    $document = new DOMDocument();
    $lastDot = strrpos($filename, '.');
    
    $htmlExtensions = array(
      '.html' => 1,
      '.htm' => 1,
    );
    
    // Allow users to override parser settings.
    if (empty($this->options['use_parser'])) {
      $useParser = '';
    }
    else {
      $useParser = strtolower($this->options['use_parser']);
    }
    
    $ext = $lastDot !== FALSE ? strtolower(substr($filename, $lastDot)) : '';
    
    try {
      set_error_handler(array('QueryPathParseException', 'initializeFromError'), $this->errTypes);
      
      // If the parser is explicitly set to XML, use that parser.
      if ($useParser == 'xml') {
        $r = $document->load($filename, $flags);
      }
      // Otherwise, see if it looks like HTML.
      elseif (isset($htmlExtensions[$ext]) || $useParser == 'html') {
        // Try parsing it as HTML.
        $r = $document->loadHTMLFile($filename);
      }
      // Default to XML.
      else {
        $r = $document->load($filename, $flags);
      }
      
    }
    // Emulate 'finally' behavior.
    catch (Exception $e) {
      restore_error_handler();
      throw $e;
    }
    restore_error_handler();
    
    
    
    /*
    if ($r == FALSE) {
      $fmt = 'Failed to load file %s: %s (%s, %s)';
      $err = error_get_last();
      if ($err['type'] & self::IGNORE_ERRORS) {
        // Need to report these somehow...
        trigger_error($err['message'], E_USER_WARNING);
      }
      else {
        throw new QueryPathParseException(sprintf($fmt, $filename, $err['message'], $err['file'], $err['line']));
      }
      
      //throw new QueryPathParseException(sprintf($fmt, $filename, $err['message'], $err['file'], $err['line']));
    }
    */
    return $document;
  }
  
  /**
   * Call extension methods.
   *
   * This function is used to invoke extension methods. It searches the
   * registered extenstensions for a matching function name. If one is found,
   * it is executed with the arguments in the $arguments array.
   * 
   * @throws QueryPathException
   *  An exception is thrown if a non-existent method is called.
   */
  public function __call($name, $arguments) {
    
    if (!QueryPathExtensionRegistry::$useRegistry) {
      throw new QueryPathException("No method named $name found (Extensions disabled).");      
    }
    
    // Loading of extensions is deferred until the first time a
    // non-core method is called. This makes constructing faster, but it
    // may make the first invocation of __call() slower (if there are 
    // enough extensions.)
    //
    // The main reason for moving this out of the constructor is that most
    // new QueryPath instances do not use extensions. Charging qp() calls
    // with the additional hit is not a good idea.
    //
    // Also, this will at least limit the number of circular references.
    if (empty($this->ext)) {
      // Load the registry
      $this->ext = QueryPathExtensionRegistry::getExtensions($this);
    }
    
    // Note that an empty ext registry indicates that extensions are disabled.
    if (!empty($this->ext) && QueryPathExtensionRegistry::hasMethod($name)) {
      $owner = QueryPathExtensionRegistry::getMethodClass($name);
      $method = new ReflectionMethod($owner, $name);
      return $method->invokeArgs($this->ext[$owner], $arguments);
    }
    throw new QueryPathException("No method named $name found. Possibly missing an extension.");
  }
  
  /**
   * Dynamically generate certain properties.
   *
   * This is used primarily to increase jQuery compatibility by providing property-like
   * behaviors.
   *
   * Currently defined properties:
   *   - length: Alias of {@link size()}.
   */
   /*
  public function __get($name) {
    switch ($name) {
      case 'length':
        return $this->size();
      default:
        throw new QueryPathException('Unknown or inaccessible property "' . $name . '" (via __get())');
    }
  }
  */
  
  /**
   * Get an iterator for the matches in this object.
   * @return Iterable
   *  Returns an iterator.
   */
  public function getIterator() {
    $i = new QueryPathIterator($this->matches);
    $i->options = $this->options;
    return $i;
  }
}

/**
 * Perform various tasks on HTML/XML entities.
 *
 * @ingroup querypath_util
 */
class QueryPathEntities {
  
  /**
   * This is three regexes wrapped into 1. The | divides them.
   * 1: Match any char-based entity. This will go in $matches[1]
   * 2: Match any num-based entity. This will go in $matches[2]
   * 3: Match any hex-based entry. This will go in $matches[3]
   * 4: Match any ampersand that is not an entity. This goes in $matches[4]
   *    This last rule will only match if one of the previous two has not already
   *    matched.
   * XXX: Are octal encodings for entities acceptable?
   */
  //protected static $regex = '/&([\w]+);|&#([\d]+);|&([\w]*[\s$]+)/m';
  protected static $regex = '/&([\w]+);|&#([\d]+);|&#(x[0-9a-fA-F]+);|(&)/m';
  
  /**
   * Replace all entities.
   * This will scan a string and will attempt to replace all
   * entities with their numeric equivalent. This will not work
   * with specialized entities.
   *
   * @param string $string
   *  The string to perform replacements on.
   * @return string
   *  Returns a string that is similar to the original one, but with 
   *  all entity replacements made.
   */
  public static function replaceAllEntities($string) {
    return preg_replace_callback(self::$regex, 'QueryPathEntities::doReplacement', $string);
  }
  
  /**
   * Callback for processing replacements.
   *
   * @param array $matches
   *  The regular expression replacement array.
   */
  protected static function doReplacement($matches) {
    // See how the regex above works out.
    //print_r($matches);

    // From count, we can tell whether we got a 
    // char, num, or bare ampersand.
    $count = count($matches);
    switch ($count) {
      case 2:
        // We have a character entity
        return '&#' . self::replaceEntity($matches[1]) . ';';
      case 3:
      case 4:
        // we have a numeric entity
        return '&#' . $matches[$count-1] . ';'; 
      case 5:
        // We have an unescaped ampersand.
        return '&#38;';
    }
  }
  
  /**
   * Lookup an entity string's numeric equivalent.
   *
   * @param string $entity
   *  The entity whose numeric value is needed.
   * @return int
   *  The integer value corresponding to the entity.
   * @author Matt Butcher
   * @author Ryan Mahoney
   */
  public static function replaceEntity($entity) {
    return self::$entity_array[$entity];
  }
  
  /**
   * Conversion mapper for entities in HTML.
   * Large entity conversion table. This is 
   * significantly broader in range than 
   * get_html_translation_table(HTML_ENTITIES).
   *
   * This code comes from Rhizome ({@link http://code.google.com/p/sinciput})
   *
   * @see get_html_translation_table()
   */
  private static $entity_array = array(
	  'nbsp' => 160, 'iexcl' => 161, 'cent' => 162, 'pound' => 163, 
	  'curren' => 164, 'yen' => 165, 'brvbar' => 166, 'sect' => 167, 
	  'uml' => 168, 'copy' => 169, 'ordf' => 170, 'laquo' => 171, 
	  'not' => 172, 'shy' => 173, 'reg' => 174, 'macr' => 175, 'deg' => 176, 
	  'plusmn' => 177, 'sup2' => 178, 'sup3' => 179, 'acute' => 180, 
	  'micro' => 181, 'para' => 182, 'middot' => 183, 'cedil' => 184, 
	  'sup1' => 185, 'ordm' => 186, 'raquo' => 187, 'frac14' => 188, 
	  'frac12' => 189, 'frac34' => 190, 'iquest' => 191, 'Agrave' => 192, 
	  'Aacute' => 193, 'Acirc' => 194, 'Atilde' => 195, 'Auml' => 196, 
	  'Aring' => 197, 'AElig' => 198, 'Ccedil' => 199, 'Egrave' => 200, 
	  'Eacute' => 201, 'Ecirc' => 202, 'Euml' => 203, 'Igrave' => 204, 
	  'Iacute' => 205, 'Icirc' => 206, 'Iuml' => 207, 'ETH' => 208, 
	  'Ntilde' => 209, 'Ograve' => 210, 'Oacute' => 211, 'Ocirc' => 212, 
	  'Otilde' => 213, 'Ouml' => 214, 'times' => 215, 'Oslash' => 216, 
	  'Ugrave' => 217, 'Uacute' => 218, 'Ucirc' => 219, 'Uuml' => 220, 
	  'Yacute' => 221, 'THORN' => 222, 'szlig' => 223, 'agrave' => 224, 
	  'aacute' => 225, 'acirc' => 226, 'atilde' => 227, 'auml' => 228, 
	  'aring' => 229, 'aelig' => 230, 'ccedil' => 231, 'egrave' => 232, 
	  'eacute' => 233, 'ecirc' => 234, 'euml' => 235, 'igrave' => 236, 
	  'iacute' => 237, 'icirc' => 238, 'iuml' => 239, 'eth' => 240, 
	  'ntilde' => 241, 'ograve' => 242, 'oacute' => 243, 'ocirc' => 244, 
	  'otilde' => 245, 'ouml' => 246, 'divide' => 247, 'oslash' => 248, 
	  'ugrave' => 249, 'uacute' => 250, 'ucirc' => 251, 'uuml' => 252, 
	  'yacute' => 253, 'thorn' => 254, 'yuml' => 255, 'quot' => 34, 
	  'amp' => 38, 'lt' => 60, 'gt' => 62, 'apos' => 39, 'OElig' => 338, 
	  'oelig' => 339, 'Scaron' => 352, 'scaron' => 353, 'Yuml' => 376, 
	  'circ' => 710, 'tilde' => 732, 'ensp' => 8194, 'emsp' => 8195, 
	  'thinsp' => 8201, 'zwnj' => 8204, 'zwj' => 8205, 'lrm' => 8206, 
	  'rlm' => 8207, 'ndash' => 8211, 'mdash' => 8212, 'lsquo' => 8216, 
	  'rsquo' => 8217, 'sbquo' => 8218, 'ldquo' => 8220, 'rdquo' => 8221, 
	  'bdquo' => 8222, 'dagger' => 8224, 'Dagger' => 8225, 'permil' => 8240, 
	  'lsaquo' => 8249, 'rsaquo' => 8250, 'euro' => 8364, 'fnof' => 402, 
	  'Alpha' => 913, 'Beta' => 914, 'Gamma' => 915, 'Delta' => 916, 
	  'Epsilon' => 917, 'Zeta' => 918, 'Eta' => 919, 'Theta' => 920, 
	  'Iota' => 921, 'Kappa' => 922, 'Lambda' => 923, 'Mu' => 924, 'Nu' => 925, 
	  'Xi' => 926, 'Omicron' => 927, 'Pi' => 928, 'Rho' => 929, 'Sigma' => 931,
	  'Tau' => 932, 'Upsilon' => 933, 'Phi' => 934, 'Chi' => 935, 'Psi' => 936,
	  'Omega' => 937, 'alpha' => 945, 'beta' => 946, 'gamma' => 947, 
	  'delta' => 948, 'epsilon' => 949, 'zeta' => 950, 'eta' => 951, 
	  'theta' => 952, 'iota' => 953, 'kappa' => 954, 'lambda' => 955, 
	  'mu' => 956, 'nu' => 957, 'xi' => 958, 'omicron' => 959, 'pi' => 960, 
	  'rho' => 961, 'sigmaf' => 962, 'sigma' => 963, 'tau' => 964, 
	  'upsilon' => 965, 'phi' => 966, 'chi' => 967, 'psi' => 968, 
	  'omega' => 969, 'thetasym' => 977, 'upsih' => 978, 'piv' => 982, 
	  'bull' => 8226, 'hellip' => 8230, 'prime' => 8242, 'Prime' => 8243, 
	  'oline' => 8254, 'frasl' => 8260, 'weierp' => 8472, 'image' => 8465, 
	  'real' => 8476, 'trade' => 8482, 'alefsym' => 8501, 'larr' => 8592, 
	  'uarr' => 8593, 'rarr' => 8594, 'darr' => 8595, 'harr' => 8596, 
	  'crarr' => 8629, 'lArr' => 8656, 'uArr' => 8657, 'rArr' => 8658, 
	  'dArr' => 8659, 'hArr' => 8660, 'forall' => 8704, 'part' => 8706, 
	  'exist' => 8707, 'empty' => 8709, 'nabla' => 8711, 'isin' => 8712, 
	  'notin' => 8713, 'ni' => 8715, 'prod' => 8719, 'sum' => 8721, 
	  'minus' => 8722, 'lowast' => 8727, 'radic' => 8730, 'prop' => 8733, 
	  'infin' => 8734, 'ang' => 8736, 'and' => 8743, 'or' => 8744, 'cap' => 8745, 
	  'cup' => 8746, 'int' => 8747, 'there4' => 8756, 'sim' => 8764, 
	  'cong' => 8773, 'asymp' => 8776, 'ne' => 8800, 'equiv' => 8801, 
	  'le' => 8804, 'ge' => 8805, 'sub' => 8834, 'sup' => 8835, 'nsub' => 8836, 
	  'sube' => 8838, 'supe' => 8839, 'oplus' => 8853, 'otimes' => 8855, 
	  'perp' => 8869, 'sdot' => 8901, 'lceil' => 8968, 'rceil' => 8969, 
	  'lfloor' => 8970, 'rfloor' => 8971, 'lang' => 9001, 'rang' => 9002, 
	  'loz' => 9674, 'spades' => 9824, 'clubs' => 9827, 'hearts' => 9829, 
	  'diams' => 9830
	);
}

/**
 * An iterator for QueryPath.
 *
 * This provides iterator support for QueryPath. You do not need to construct
 * a QueryPathIterator. QueryPath does this when its {@link QueryPath::getIterator()}
 * method is called.
 *
 * @ingroup querypath_util
 */
class QueryPathIterator extends IteratorIterator {
  public $options = array();
  private $qp = NULL;
  
  public function current() {
    if (!isset($this->qp)) {
      $this->qp = qp(parent::current(), NULL, $this->options);
    }
    else {
      $splos = new SplObjectStorage();
      $splos->attach(parent::current());
      $this->qp->setMatches($splos);
    }
    return $this->qp;
  }
}

/**
 * Manage default options.
 *
 * This class stores the default options for QueryPath. When a new 
 * QueryPath object is constructed, options specified here will be 
 * used.
 *
 * <b>Details</b>
 * This class defines no options of its own. Instead, it provides a 
 * central tool for developers to override options set by QueryPath.
 * When a QueryPath object is created, it will evaluate options in the 
 * following order:
 *
 * - Options passed into {@link qp()} have highest priority.
 * - Options in {@link QueryPathOptions} (this class) have the next highest priority.
 * - If the option is not specified elsewhere, QueryPath will use its own defaults.
 *
 * @see qp()
 * @see QueryPathOptions::set()
 * @ingroup querypath_util
 */
class QueryPathOptions {
  
  /**
   * This is the static options array.
   *
   * Use the {@link set()}, {@link get()}, and {@link merge()} to
   * modify this array.
   */
  static $options = array();
  
  /**
   * Set the default options.
   *
   * The passed-in array will be used as the default options list.
   *
   * @param array $array
   *  An associative array of options.
   */
  static function set($array) {
    self::$options = $array;
  }
  
  /**
   * Get the default options.
   *
   * Get all options currently set as default.
   *
   * @return array
   *  An array of options. Note that only explicitly set options are 
   *  returned. {@link QueryPath} defines default options which are not
   *  stored in this object.
   */
  static function get() {
    return self::$options;
  }
  
  /**
   * Merge the provided array with existing options.
   *
   * On duplicate keys, the value in $array will overwrite the 
   * value stored in the options.
   *
   * @param array $array
   *  Associative array of options to merge into the existing options.
   */
  static function merge($array) {
    self::$options = $array + self::$options;
  }
  
  /**
   * Returns true of the specified key is already overridden in this object.
   *
   * @param string $key
   *  The key to search for.
   */
  static function has($key) {
    return array_key_exists($key, self::$options);
  }
  
}

/**
 * Exception indicating that a problem has occured inside of a QueryPath object.
 *
 * @ingroup querypath_core
 */
class QueryPathException extends Exception {}

/**
 * Exception indicating that a parser has failed to parse a file.
 *
 * This will report parser warnings as well as parser errors. It should only be 
 * thrown, though, under error conditions.
 *
 * @ingroup querypath_core
 */
class QueryPathParseException extends QueryPathException {
  const ERR_MSG_FORMAT = 'Parse error in %s on line %d column %d: %s (%d)';
  const WARN_MSG_FORMAT = 'Parser warning in %s on line %d column %d: %s (%d)';
  // trigger_error
  public function __construct($msg = '', $code = 0, $file = NULL, $line = NULL) {

    $msgs = array();
    foreach(libxml_get_errors() as $err) {
      $format = $err->level == LIBXML_ERR_WARNING ? self::WARN_MSG_FORMAT : self::ERR_MSG_FORMAT;
      $msgs[] = sprintf($format, $err->file, $err->line, $err->column, $err->message, $err->code);
    }
    $msg .= implode("\n", $msgs);
    
    if (isset($file)) {
      $msg .= ' (' . $file;
      if (isset($line)) $msg .= ': ' . $line;
      $msg .= ')';
    }
    
    parent::__construct($msg, $code);
  }
  
  public static function initializeFromError($code, $str, $file, $line, $cxt) {
    //printf("\n\nCODE: %s %s\n\n", $code, $str);
    $class = __CLASS__;
    throw new $class($str, $code, $file, $line);
  }
}

/**
 * Indicates that an input/output exception has occurred.
 *
 * @ingroup querypath_core
 */
class QueryPathIOException extends QueryPathParseException {
  public static function initializeFromError($code, $str, $file, $line, $cxt) {
    $class = __CLASS__;
    throw new $class($str, $code, $file, $line);
  }
  
}