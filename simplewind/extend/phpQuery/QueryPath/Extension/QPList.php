<?php
/** @file
 * This extension provides support for common HTML list operations.
 */

/**
 * Provide list operations for QueryPath.
 *
 * The QPList class is an extension to QueryPath. It provides HTML list generators
 * that take lists and convert them into bulleted lists inside of QueryPath.
 *
 * @deprecated This will be removed from a subsequent version of QueryPath. It will
 *  be released as a stand-alone extension.
 * @ingroup querypath_extensions
 */ 
class QPList implements QueryPathExtension {
  const UL = 'ul';
  const OL = 'ol';
  const DL = 'dl';
  
  protected $qp = NULL;
  public function __construct(QueryPath $qp) {
    $this->qp = $qp;
  }
  
  public function appendTable($items, $options = array()) {
    $opts = $options + array(
      'table class' => 'qptable',
    );
    $base = '<?xml version="1.0"?>
    <table>
    <tbody>
      <tr></tr>
    </tbody>
    </table>';
    
    $qp = qp($base, 'table')->addClass($opts['table class'])->find('tr');
    if ($items instanceof TableAble) {
      $headers = $items->getHeaders();
      $rows = $items->getRows();
    }
    elseif ($items instanceof Traversable) {
      $headers = array();
      $rows = $items;
    }
    else {
      $headers = $items['headers'];
      $rows = $items['rows'];
    }
    
    // Add Headers:
    foreach ($headers as $header) {
      $qp->append('<th>' . $header . '</th>');
    }
    $qp->top()->find('tr:last');
    
    // Add rows and cells.
    foreach ($rows as $row) {
      $qp->after('<tr/>')->next();
      foreach($row as $cell) $qp->append('<td>' . $cell . '</td>');
    }
    
    $this->qp->append($qp->top());
    
    return $this->qp;
  }
  
  /**
   * Append a list of items into an HTML DOM using one of the HTML list structures.
   * This takes a one-dimensional array and converts it into an HTML UL or OL list,
   * <b>or</b> it can take an associative array and convert that into a DL list.
   *
   * In addition to arrays, this works with any Traversable or Iterator object.
   *
   * OL/UL arrays can be nested.
   *
   * @param mixed $items
   *   An indexed array for UL and OL, or an associative array for DL. Iterator and
   *  Traversable objects can also be used.
   * @param string $type
   *  One of ul, ol, or dl. Predefined constants are available for use.
   * @param array $options
   *  An associative array of configuration options. The supported options are:
   *  - 'list class': The class that will be assigned to a list.
   */
  public function appendList($items, $type = self::UL, $options = array()) {
    $opts = $options + array(
      'list class' => 'qplist',
    );
    if ($type == self::DL) {
      $q = qp('<?xml version="1.0"?><dl></dl>', 'dl')->addClass($opts['list class']);
      foreach ($items as $dt => $dd) {
        $q->append('<dt>' . $dt . '</dt><dd>' . $dd . '</dd>');
      }
      $q->appendTo($this->qp);
    }
    else {
      $q = $this->listImpl($items, $type, $opts);
      $this->qp->append($q->find(':root'));
    }
    
    return $this->qp;
  }
  
  /**
   * Internal recursive list generator for appendList.
   */
  protected function listImpl($items, $type, $opts, $q = NULL) {
    $ele = '<' . $type . '/>';
    if (!isset($q))
      $q = qp()->append($ele)->addClass($opts['list class']);
          
    foreach ($items as $li) {
      if ($li instanceof QueryPath) {
        $q = $this->listImpl($li->get(), $type, $opts, $q);
      }
      elseif (is_array($li) || $li instanceof Traversable) {
        $q->append('<li><ul/></li>')->find('li:last > ul');
        $q = $this->listImpl($li, $type, $opts, $q);
        $q->parent();
      }
      else {
        $q->append('<li>' . $li . '</li>');
      }
    }
    return $q;
  }
  
  /**
   * Unused.
   */
  protected function isAssoc($array) {
    // A clever method from comment on is_array() doc page:
    return count(array_diff_key($array, range(0, count($array) - 1))) != 0; 
  }
}
QueryPathExtensionRegistry::extend('QPList');

/**
 * A TableAble object represents tabular data and can be converted to a table.
 *
 * The {@link QPList} extension to {@link QueryPath} provides a method for
 * appending a table to a DOM ({@link QPList::appendTable()}).
 *
 * Implementing classes should provide methods for getting headers, rows
 * of data, and the number of rows in the table ({@link TableAble::size()}).
 * Implementors may also choose to make classes Iterable or Traversable over
 * the rows of the table.
 *
 * Two very basic implementations of TableAble are provided in this package:
 *  - {@link QPTableData} provides a generic implementation.
 *  - {@link QPTableTextData} provides a generic implementation that also escapes
 *    all data.
 */
interface TableAble {
  public function getHeaders();
  public function getRows();
  public function size();
}

/**
 * Format data to be inserted into a simple HTML table.
 *
 * Data in the headers or rows may contain markup. If you want to 
 * disallow markup, use a {@see QPTableTextData} object instead.
 */
class QPTableData implements TableAble, IteratorAggregate {
  
  protected $headers;
  protected $rows;
  protected $caption;
  protected $p = -1;
  
  public function setHeaders($array) {$this->headers = $array; return $this;}
  public function getHeaders() {return $this->headers; }
  public function setRows($array) {$this->rows = $array; return $this;}
  public function getRows() {return $this->rows;}
  public function size() {return count($this->rows);}
  public function getIterator() {
    return new ArrayIterator($rows);
  }
}

/**
 * Provides a table where all of the headers and data are treated as text data.
 * 
 * This provents marked-up data from being inserted into the DOM as elements. 
 * Instead, the text is escaped using {@see htmlentities()}.
 *
 * @see QPTableData
 */
class QPTableTextData extends QPTableData {
  public function setHeaders($array) {
    $headers = array();
    foreach ($array as $header) {
      $headers[] = htmlentities($header);
    }
    parent::setHeaders($headers);
    return $this;
  }
  public function setRows($array) {
    $count = count($array);
    for ($i = 0; $i < $count; ++$i) {
      $cols = array();
      foreach ($data[$i] as $datum) {
        $cols[] = htmlentities($datum);
      }
      $data[$i] = $cols;
    }
    parent::setRows($array);
    return $this;
  }
}