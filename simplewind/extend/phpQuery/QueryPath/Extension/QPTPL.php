<?php
/** @file
 * QueryPath templates. See QPTPL.
 */
/**
 * QPTPL is a template library for QueryPath.
 *
 * The QPTPL extension provides template tools that can be used in 
 * conjunction with QueryPath.
 *
 * There are two basic modes in which this tool operates. Both merge data into
 * a pure HTML template. Both base their insertions on classes and IDs in the
 * HTML data. Where they differ is in the kind of data merged into the template.
 *
 * One mode takes array data and does a deep (recursive) merge into the template.
 * It can be used for simple substitutions, but it can also be used to loop through
 * "rows" of data and create tables.
 *
 * The second mode takes a classed object and introspects that object to find out
 * what CSS classes it is capable of filling. This is one way of bridging an object
 * model and QueryPath data.
 *
 * The unit tests are a good place for documentation, as is the QueryPath webste.
 *
 * @author M Butcher <matt@aleph-null.tv>
 * @license http://opensource.org/licenses/lgpl-2.1.php LGPL or MIT-like license.
 * @see QueryPathExtension
 * @see QueryPathExtensionRegistry::extend()
 * @see https://fedorahosted.org/querypath/wiki/QueryPathTemplate
 * @ingroup querypath_extensions
 */
class QPTPL implements QueryPathExtension {
  protected $qp;
  public function __construct(QueryPath $qp) {
    $this->qp = $qp;
  }
  
  /**
   * Apply a template to an object and then insert the results.
   *
   * This takes a template (an arbitrary fragment of XML/HTML) and an object
   * or array and inserts the contents of the object into the template. The 
   * template is then appended to all of the nodes in the current list.
   *
   * Note that the data in the object is *not* escaped before it is merged 
   * into the template. For that reason, an object can return markup (as 
   * long as it is well-formed).
   * 
   * @param mixed $template
   *  The template. It can be of any of the types that {@link qp()} supports
   *  natively. Typically it is a string of XML/HTML.
   * @param mixed $object
   *  Either an object or an associative array. 
   *  - In the case where the parameter
   *  is an object, this will introspect the object, looking for getters (a la
   *  Java bean behavior). It will then search the document for CSS classes
   *  that match the method name. The function is then executed and its contents
   *  inserted into the document. (If the function returns NULL, nothing is 
   *  inserted.)
   *  - In the case where the paramter is an associative array, the function will
   *  look through the template for CSS classes that match the keys of the 
   *  array. When an array key is found, the array value is inserted into the 
   *  DOM as a child of the currently matched element(s).
   * @param array $options
   *  The options for this function. Valid options are:
   *  - <None defined yet>
   * @return QueryPath
   *  Returns a QueryPath object with all of the changes from the template
   *  applied into the QueryPath elements.
   * @see QueryPath::append()
   */
  public function tpl($template, $object, $options = array()) {
    // Handle default options here.

    //$tqp = ($template instanceof QueryPath) ? clone $template: qp($template);
    $tqp = qp($template);
    
    if (is_array($object) || $object instanceof Traversable) {
      $this->tplArrayR($tqp, $object, $options);
      return $this->qp->append($tqp->top());
    }
    elseif (is_object($object)) {
      $this->tplObject($tqp, $object, $options);
    }
    
    return $this->qp->append($tqp->top());
  }
  
  /**
   * Given one template, do substitutions for all objects.
   *
   * Using this method, one template can be populated from a variety of 
   * sources. That one template is then appended to the QueryPath object.
   * @see tpl()
   * @param mixed $template
   *  The template. It can be of any of the types that {@link qp()} supports
   *  natively. Typically it is a string of XML/HTML.
   * @param array $objects
   *  An indexed array containing a list of objects or arrays (See {@link tpl()})
   *  that will be merged into the template.
   * @param array $options
   *  An array of options. See {@link tpl()} for a list.
   * @return QueryPath
   *  Returns the QueryPath object.
   */
  public function tplAll($template, $objects, $options = array()) {
    $tqp = qp($template, ':root');
    foreach ($objects as $object) {
      if (is_array($object)) 
        $tqp = $this->tplArrayR($tqp, $object, $options);
      elseif (is_object($object)) 
        $tqp = $this->tplObject($tqp, $object, $options);
    }
    return $this->qp->append($tqp->top());
  }
  
  /*
  protected function tplArray($tqp, $array, $options = array()) {
    
    // If we find something that's not an array, we try to handle it.
    if (!is_array($array)) {
     is_object($array) ? $this->tplObject($tqp, $array, $options) : $tqp->append($array);
    }
    // An assoc array means we have mappings of classes to content.
    elseif ($this->isAssoc($array)) {
      print 'Assoc array found.' . PHP_EOL;
      foreach ($array as $key => $value) {
        $first = substr($key,0,1);

        // We allow classes and IDs if explicit. Otherwise we assume
        // a class.
        if ($first != '.' && $first != '#') $key = '.' . $key;
        
        if ($tqp->top()->find($key)->size() > 0) {
          print "Value: " . $value . PHP_EOL;
          if (is_array($value)) {
            //$newqp = qp($tqp)->cloneAll();
            print $tqp->xml();
            $this->tplArray($tqp, $value, $options);
            print "Finished recursion\n";
          }
          else {
            print 'QP is ' . $tqp->size() . " inserting value: " . $value . PHP_EOL;
            
            $tqp->append($value);
          }
        }
      }
    }
    // An indexed array means we have multiple instances of class->content matches.
    // We copy the portion of the template and then call repeatedly.
    else {
      print "Array of arrays found..\n";
      foreach ($array as $array2) {
        $clone = qp($tqp->xml());
        $this->tplArray($clone, $array2, $options);
        print "Now appending clone.\n" . $clone->xml();
        $tqp->append($clone->parent());
      }
    }
    
    
    //return $tqp->top();
    return $tqp;
  }
  */
  
  /**
   * Introspect objects to map their functions to CSS classes in a template.
   */
  protected function tplObject($tqp, $object, $options = array()) {
    $ref = new ReflectionObject($object);
    $methods = $ref->getMethods();
    foreach ($methods as $method) {
      if (strpos($method->getName(), 'get') === 0) {
        $cssClass = $this->method2class($method->getName());
        if ($tqp->top()->find($cssClass)->size() > 0) {
          $tqp->append($method->invoke($object));
        }
        else {
          // Revert to the find() that found something.
          $tqp->end();
        }
      }
    }
    //return $tqp->top();
    return $tqp;
  }
  
  /**
   * Recursively merge array data into a template.
   */
  public function tplArrayR($qp, $array, $options = NULL) {
    // If the value looks primitive, append it.
    if (!is_array($array) && !($array instanceof Traversable)) {
      $qp->append($array);
    }
    // If we are dealing with an associative array, traverse it
    // and merge as we go.
    elseif ($this->isAssoc($array)) {
      // Do key/value substitutions
      foreach ($array as $k => $v) {
        
        // If no dot or hash, assume class.
        $first = substr($k,0,1);
        if ($first != '.' && $first != '#') $k = '.' . $k;
        
        // If value is an array, recurse.
        if (is_array($v)) {
          // XXX: Not totally sure that starting at the 
          // top is right. Perhaps it should start
          // at some other context?
          $this->tplArrayR($qp->top($k), $v, $options);
        }
        // Otherwise, try to append value.
        else {
          $qp->branch()->children($k)->append($v);
        }
      }
    }
    // Otherwise we have an indexed array, and we iterate through
    // it.
    else {
      // Get a copy of the current template and then recurse.
      foreach ($array as $entry) {
        $eles = $qp->get();
        $template = array();
        
        // We manually deep clone the template.
        foreach ($eles as $ele) {
          $template = $ele->cloneNode(TRUE);
        }
        $tpl = qp($template);
        $tpl = $this->tplArrayR($tpl, $entry, $options);
        $qp->before($tpl);
      }
      // Remove the original template without loosing a handle to the
      // newly injected one.
      $dead = $qp->branch();
      $qp->parent();
      $dead->remove();
      unset($dead);
    }
    return $qp;
  }
  
  /**
   * Check whether an array is associative.
   * If the keys of the array are not consecutive integers starting with 0,
   * this will return false.
   *
   * @param array $array
   *  The array to test.
   * @return Boolean
   *  TRUE if this is an associative array, FALSE otherwise.
   */
  public function isAssoc($array) {
    $i = 0;
    foreach ($array as $k => $v) if ($k !== $i++) return TRUE;
    // If we get here, all keys passed.
    return FALSE;
  }

  /**
   * Convert a function name to a CSS class selector (e.g. myFunc becomes '.myFunc').
   * @param string $mname
   *  Method name.
   * @return string
   *  CSS 3 Class Selector.
   */
  protected function method2class($mname) {
    return '.' . substr($mname, 3);
  }
}
QueryPathExtensionRegistry::extend('QPTPL');