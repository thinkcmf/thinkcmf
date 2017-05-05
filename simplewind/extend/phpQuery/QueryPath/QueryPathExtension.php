<?php
/** @file
 * This file contains the Query Path extension tools.
 *
 * Query Path can be extended to support additional features. To do this, 
 * you need only create a new class that implements {@link QueryPathExtension}
 * and add your own methods. This class can then be registered as an extension. 
 * It will then be available through Query Path.
 *
 * For information on building your own extension, see {@link QueryPathExtension}.
 * If you are trying to load an extension you have downloaded, chances are good that
 * all you need to do is {@link require_once} the file that contains the extension.
 *
 * @author M Butcher <matt@aleph-null.tv>
 * @license http://opensource.org/licenses/lgpl-2.1.php LGPL or MIT-like license.
 * @see QueryPathExtension
 * @see QueryPathExtensionRegistry::extend()
 */

/** @addtogroup querypath_extensions Extensions
 * The QueryPath extension system and bundled extensions.
 *
 * Much like jQuery, QueryPath provides a simple extension mechanism that allows 
 * extensions to auto-register themselves upon being loaded. For a simple example, see
 * QPXML. For the internals, see QueryPathExntesion and QueryPath::__construct().
 */

/**
 * A QueryPathExtension is a tool that extends the capabilities of a QueryPath object.
 *
 * Extensions to QueryPath should implement the QueryPathExtension interface. The
 * only requirement is that the extension provide a constructor that takes a
 * QueryPath object as a parameter.
 *
 * Here is an example QueryPath extension:
 * <code><?php
 * class StubExtensionOne implements QueryPathExtension {
 *   private $qp = NULL;
 *   public function __construct(QueryPath $qp) {
 *     $this->qp = $qp;
 *   }
 *
 *   public function stubToe() {
 *     $this->qp->find(':root')->append('<toe/>')->end();
 *     return $this->qp;
 *   }
 * }
 * QueryPathExtensionRegistry::extend('StubExtensionOne');
 * ?></code>
 * In this example, the StubExtensionOne class implements QueryPathExtension.
 * The constructor stores a local copyof the QueryPath object. This is important
 * if you are planning on fully integrating with QueryPath's Fluent Interface.
 *
 * Finally, the stubToe() function illustrates how the extension makes use of 
 * QueryPath internally, and remains part of the fluent interface by returning
 * the $qp object.
 *
 * Notice that beneath the class, there is a single call to register the 
 * extension with QueryPath's registry. Your extension should end with a line 
 * similar to this.
 *
 * <b>How is a QueryPath extension called?</b>
 *
 * QueryPath extensions are called like regular QueryPath functions. For 
 * example, the extension above can be called like this:
 * <code>
 * qp('some.xml')->stubToe();
 * </code>
 * Since it returns the QueryPath ($qp) object, chaining is supported:
 * <code>
 * print qp('some.xml')->stubToe()->xml();
 * </code>
 * When you write your own extensions, anything that does not need to return a 
 * specific value should return the QueryPath object. Between that and the 
 * extension registry, this will provide the best developer experience.
 *
 * @ingroup querypath_extensions
 */
interface QueryPathExtension {
  public function __construct(QueryPath $qp);
}

/**
 * A registry for QueryPath extensions.
 *
 * QueryPath extensions should call the {@link QueryPathExtensionRegistry::extend()}
 * function to register their extension classes. The QueryPath library then 
 * uses this information to determine what QueryPath extensions should be loaded and
 * executed.
 *
 * @ingroup querypath_extensions
 */
class QueryPathExtensionRegistry {
  /**
   * Internal flag indicating whether or not the registry should
   * be used for automatic extension loading. If this is false, then
   * implementations should not automatically load extensions.
   */
  public static $useRegistry = TRUE;
  /**
   * The extension registry. This should consist of an array of class
   * names.
   */
  protected static $extensionRegistry = array();
  protected static $extensionMethodRegistry = array();
  /**
   * Extend QueryPath with the given extension class.
   */
  public static function extend($classname) {
    self::$extensionRegistry[] = $classname;
    $class = new ReflectionClass($classname);
    $methods = $class->getMethods();
    foreach ($methods as $method) {
      self::$extensionMethodRegistry[$method->getName()] = $classname;
    }
  }
  
  /**
   * Check to see if a method is known.
   * This checks to see if the given method name belongs to one of the 
   * registered extensions. If it does, then this will return TRUE.
   *
   * @param string $name
   *  The name of the method to search for.
   * @return boolean
   *  TRUE if the method exists, false otherwise.
   */
  public static function hasMethod($name) {
    return isset(self::$extensionMethodRegistry[$name]);
  }
  
  /**
   * Check to see if the given extension class is registered.
   * Given a class name for a {@link QueryPathExtension} class, this 
   * will check to see if that class is registered. If so, it will return
   * TRUE.
   * 
   * @param string $name
   *  The name of the class.
   * @return boolean
   *  TRUE if the class is registered, FALSE otherwise.
   */
  public static function hasExtension($name) {
    return in_array($name, self::$extensionRegistry);
  }
  
  /**
   * Get the class that a given method belongs to.
   * Given a method name, this will check all registered extension classes
   * to see if any of them has the named method. If so, this will return 
   * the classname.
   *
   * Note that if two extensions are registered that contain the same 
   * method name, the last one registred will be the only one recognized.
   *
   * @param string $name
   *  The name of the method.
   * @return string
   *  The name of the class.
   */
  public static function getMethodClass($name) {
    return self::$extensionMethodRegistry[$name];
  }
  
  /**
   * Get extensions for the given QueryPath object.
   *
   * Given a {@link QueryPath} object, this will return
   * an associative array of extension names to (new) instances.
   * Generally, this is intended to be used internally.
   *
   * @param QueryPath $qp
   *  The QueryPath into which the extensions should be registered.
   * @return array
   *  An associative array of classnames to instances.
   */
  public static function getExtensions(QueryPath $qp) {
    $extInstances = array();
    foreach (self::$extensionRegistry as $ext) {
      $extInstances[$ext] = new $ext($qp);
    }
    return $extInstances;
  }
  
  /**
   * Enable or disable automatic extension loading.
   *
   * If extension autoloading is disabled, then QueryPath will not 
   * automatically load all registred extensions when a new QueryPath
   * object is created using {@link qp()}.
   */
  public static function autoloadExtensions($boolean = TRUE) {
    self::$useRegistry = $boolean;
  }
}