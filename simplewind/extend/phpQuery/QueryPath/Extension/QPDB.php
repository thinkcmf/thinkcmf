<?php
/** @file
 * This package contains classes for handling database transactions from
 * within QueryPath.
 *
 * The tools here use the PDO (PHP Data Objects) library to execute database
 * functions.
 *
 * Using tools in this package, you can write QueryPath database queries
 * that query an RDBMS and then insert the results into the document.
 *
 * Example:
 *
 * @code
 * <?php
 * $template = '<?xml version="1.0"?><tr><td class="colOne"/><td class="colTwo"/><td class="colThree"/></tr>';
 * $qp = qp(QueryPath::HTML_STUB, 'body') // Open a stub HTML doc and select <body/>
 *  ->append('<table><tbody/></table>')
 *  ->dbInit($this->dsn)
 *  ->queryInto('SELECT * FROM qpdb_test WHERE 1', array(), $template)
 *  ->doneWithQuery()
 *  ->writeHTML();
 * ?>
 * @endcode
 *
 * The code above will take the results of a SQL query and insert them into a n
 * HTML table.
 *
 * If you are doing many database operations across multiple QueryPath objects,
 * it is better to avoid using {@link QPDB::dbInit()}. Instead, you should 
 * call the static {@link QPDB::baseDB()} method to configure a single database
 * connection that can be shared by all {@link QueryPath} instances.
 *
 * Thus, we could rewrite the above to look like this:
 * @code
  * <?php
  * QPDB::baseDB($someDN);
  *
  * $template = '<?xml version="1.0"?><tr><td class="colOne"/><td class="colTwo"/><td class="colThree"/></tr>';
  * $qp = qp(QueryPath::HTML_STUB, 'body') // Open a stub HTML doc and select <body/>
  *  ->append('<table><tbody/></table>')
  *  ->queryInto('SELECT * FROM qpdb_test WHERE 1', array(), $template)
  *  ->doneWithQuery()
  *  ->writeHTML();
  * ?>
  * @endcode
 *
 * Note that in this case, the QueryPath object doesn't need to call a method to
 * activate the database. There is no call to {@link dbInit()}. Instead, it checks
 * the base class to find the shared database.
 *
 * (Note that if you were to add a dbInit() call to the above, it would create
 * a new database connection.)
 *
 * The result of both of these examples will be identical.
 * The output looks something like this:
 * 
 * @code
 * <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
 * <html xmlns="http://www.w3.org/1999/xhtml">
 * <head>
 *  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"></meta>
 * 	<title>Untitled</title>
 * </head>
 *<body>
 *<table>
 * <tbody>
 *  <tr>
 *   <td class="colOne">Title 0</td>
 *   <td class="colTwo">Body 0</td>
 *   <td class="colThree">Footer 0</td>
 *  </tr>
 *  <tr>
 *   <td class="colOne">Title 1</td>
 *   <td class="colTwo">Body 1</td>
 *   <td class="colThree">Footer 1</td>
 *  </tr>
 *  <tr>
 *   <td class="colOne">Title 2</td>
 *   <td class="colTwo">Body 2</td>
 *   <td class="colThree">Footer 2</td>
 *  </tr>
 *  <tr>
 *   <td class="colOne">Title 3</td>
 *   <td class="colTwo">Body 3</td>
 *   <td class="colThree">Footer 3</td>
 *  </tr>
 *  <tr>
 *   <td class="colOne">Title 4</td>
 *   <td class="colTwo">Body 4</td>
 *   <td class="colThree">Footer 4</td>
 *  </tr>
 * </tbody>
 *</table>
 *</body>
 *</html>
 * @endcode
 *
 * Note how the CSS classes are used to correlate DB table names to template
 * locations.
 *
 * 
 * @author M Butcher <matt@aleph-null.tv>
 * @license http://opensource.org/licenses/lgpl-2.1.php LGPL or MIT-like license.
 * @see QueryPathExtension
 * @see QueryPathExtensionRegistry::extend()
 * @see QPDB
 */
 
/**
 * Provide DB access to a QueryPath object.
 *
 * This extension provides tools for communicating with a database using the 
 * QueryPath library. It relies upon PDO for underlying database communiction. This
 * means that it supports all databases that PDO supports, including MySQL, 
 * PostgreSQL, and SQLite.
 *
 * Here is an extended example taken from the unit tests for this library.
 * 
 * Let's say we create a database with code like this:
 * @code
 *<?php
 * public function setUp() {
 *   $this->db = new PDO($this->dsn);
 *   $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 *   $this->db->exec('CREATE TABLE IF NOT EXISTS qpdb_test (colOne, colTwo, colThree)');
 *   
 *   $stmt = $this->db->prepare(
 *     'INSERT INTO qpdb_test (colOne, colTwo, colThree) VALUES (:one, :two, :three)'
 *   );
 *   
 *   for ($i = 0; $i < 5; ++$i) {
 *     $vals = array(':one' => 'Title ' . $i, ':two' => 'Body ' . $i, ':three' => 'Footer ' . $i);
 *     $stmt->execute($vals);
 *     $stmt->closeCursor();
 *   }
 * }
 * ?>
 * @endcode
 * 
 * From QueryPath with QPDB, we can now do very elaborate DB chains like this:
 *
 * @code
 * <?php
 * $sql = 'SELECT * FROM qpdb_test';
 * $args = array();
 * $qp = qp(QueryPath::HTML_STUB, 'body') // Open a stub HTML doc and select <body/>
 *   ->append('<h1></h1>') // Add <h1/>
 *   ->children()  // Select the <h1/>
 *   ->dbInit($this->dsn) // Connect to the database
 *   ->query($sql, $args) // Execute the SQL query
 *   ->nextRow()  // Select a row. By default, no row is selected.
 *   ->appendColumn('colOne') // Append Row 1, Col 1 (Title 0)
 *   ->parent() // Go back to the <body/>
 *   ->append('<p/>') // Append a <p/> to the body
 *   ->find('p')  // Find the <p/> we just created.
 *   ->nextRow() // Advance to row 2
 *   ->prependColumn('colTwo') // Get row 2, col 2. (Body 1)
 *   ->columnAfter('colThree') // Get row 2 col 3. (Footer 1)
 *   ->doneWithQuery() // Let QueryPath clean up. YOU SHOULD ALWAYS DO THIS.
 *   ->writeHTML(); // Write the output as HTML.
 * ?> 
 * @endcode
 *
 * With the code above, we step through the document, selectively building elements
 * as we go, and then populating this elements with data from our initial query.
 *
 * When the last command, {@link QueryPath:::writeHTML()}, is run, we will get output
 * like this:
 * 
 * @code
 *   <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
 *   <html xmlns="http://www.w3.org/1999/xhtml">
 *     <head>
 *     	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
 *     	<title>Untitled</title>
 *     </head>
 *     <body>
 *       <h1>Title 0</h1>
 *       <p>Body 1</p>
 *       Footer 1</body>
 *    </html>
 * @endcode
 *
 * Notice the body section in particular. This is where the data has been
 * inserted.
 *
 * Sometimes you want to do something a lot simpler, like give QueryPath a 
 * template and have it navigate a query, inserting the data into a template, and
 * then inserting the template into the document. This can be done simply with 
 * the {@link queryInto()} function.
 *
 * Here's an example from another unit test:
 *
 * @code
 * <?php
 * $template = '<?xml version="1.0"?><li class="colOne"/>';
 * $sql = 'SELECT * FROM qpdb_test';
 * $args = array();
 * $qp = qp(QueryPath::HTML_STUB, 'body')
 *   ->append('<ul/>') // Add a new <ul/>
 *   ->children() // Select the <ul/>
 *   ->dbInit($this->dsn) // Initialize the DB
 *   // BIG LINE: Query the results, run them through the template, and insert them.
 *   ->queryInto($sql, $args, $template) 
 *   ->doneWithQuery()
 *   ->writeHTML(); // Write the results as HTML.
 * ?>
 * @endcode 
 *
 * The simple code above puts the first column of the select statement
 * into an unordered list. The example output looks like this:
 *
 * @code
 * <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
 * <html xmlns="http://www.w3.org/1999/xhtml">
 *   <head>
 *   	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"></meta>
 *   	<title>Untitled</title>
 *    </head>
 *    <body>
 *    <ul>
 *    <li class="colOne">Title 0</li>
 *    <li class="colOne">Title 1</li>
 *    <li class="colOne">Title 2</li>
 *    <li class="colOne">Title 3</li>
 *    <li class="colOne">Title 4</li>
 *    </ul>
 *   </body>
 * </html>
 * @endcode
 *
 * Typical starting methods for this class are {@link QPDB::baseDB()}, 
 * {@link QPDB::query()}, and {@link QPDB::queryInto()}.
 *
 * @ingroup querypath_extensions
 */
class QPDB implements QueryPathExtension {
  protected $qp;
  protected $dsn;
  protected $db;
  protected $opts;
  protected $row = NULL;
  protected $stmt = NULL;
  
  protected static $con = NULL;
  
  /**
   * Create a new database instance for all QueryPath objects to share.
   *
   * This method need be called only once. From there, other QPDB instances
   * will (by default) share the same database instance.
   *
   * Normally, a DSN should be passed in. Username, password, and db params
   * are all passed in using the options array.
   *
   * On rare occasions, it may be more fitting to pass in an existing database
   * connection (which must be a {@link PDO} object). In such cases, the $dsn
   * parameter can take a PDO object instead of a DSN string. The standard options
   * will be ignored, though.
   *
   * <b>Warning:</b> If you pass in a PDO object that is configured to NOT throw
   * exceptions, you will need to handle error checking differently.
   *
   * <b>Remember to always use {@link QPDB::doneWithQuery()} when you are done
   * with a query. It gives PDO a chance to clean up open connections that may
   * prevent other instances from accessing or modifying data.</b>
   *
   * @param string $dsn
   *  The DSN of the database to connect to. You can also pass in a PDO object, which
   *  will set the QPDB object's database to the one passed in.
   * @param array $options
   *  An array of configuration options. The following options are currently supported:
   *  - username => (string)
   *  - password => (string)
   *  - db params => (array) These will be passed into the new PDO object.
   *    See the PDO documentation for a list of options. By default, the
   *    only flag set is {@link PDO::ATTR_ERRMODE}, which is set to 
   *    {@link PDO::ERRMODE_EXCEPTION}.
   * @throws PDOException
   *  An exception may be thrown if the connection cannot be made.
   */
  static function baseDB($dsn, $options = array()) {
    
    $opts = $options + array(
      'username' => NULL,
      'password' => NULL,
      'db params' => array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION),
    );
    
    // Allow this to handle the case where an outside
    // connection does the initialization.
    if ($dsn instanceof PDO) {
      self::$con = $dsn;
      return;
    }
    self::$con = new PDO($dsn, $opts['username'], $opts['password'], $opts['db params']);
  }
  
  /**
   * 
   * This method may be used to share the connection with other, 
   * non-QueryPath objects.
   */
  static function getBaseDB() {return self::$con;}
  
  /**
   * Used to control whether or not all rows in a result should be cycled through.
   */
  protected $cycleRows = FALSE;
  
  /**
   * Construct a new QPDB object. This is usually done by QueryPath itself.
   */
  public function __construct(QueryPath $qp) {
    $this->qp = $qp;
    // By default, we set it up to use the base DB.
    $this->db = self::$con;
  }
  
  /**
   * Create a new connection to the database. Use the PDO DSN syntax for a 
   * connection string.
   *
   * This creates a database connection that will last for the duration of 
   * the QueryPath object. This method ought to be used only in two cases:
   * - When you will only run a couple of queries during the life of the 
   *   process.
   * - When you need to connect to a database that will only be used for 
   *   a few things.
   * Otherwise, you should use {@link QPDB::baseDB} to configure a single
   * database connection that all of {@link QueryPath} can share.
   *
   * <b>Remember to always use {@link QPDB::doneWithQuery()} when you are done
   * with a query. It gives PDO a chance to clean up open connections that may
   * prevent other instances from accessing or modifying data.</b>
   *
   * @param string $dsn
   *  The PDO DSN connection string.
   * @param array $options
   *  Connection options. The following options are supported:
   *  - username => (string)
   *  - password => (string)
   *  - db params => (array) These will be passed into the new PDO object.
   *    See the PDO documentation for a list of options. By default, the
   *    only flag set is {@link PDO::ATTR_ERRMODE}, which is set to 
   *    {@link PDO::ERRMODE_EXCEPTION}.
   * @return QueryPath
   *  The QueryPath object.
   * @throws PDOException
   *  The PDO library is configured to throw exceptions, so any of the 
   *  database functions may throw a PDOException.
   */
  public function dbInit($dsn, $options = array()) {
    $this->opts = $options + array(
      'username' => NULL,
      'password' => NULL,
      'db params' => array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION),
    );
    $this->dsn = $dsn;
    $this->db = new PDO($dsn, $this->opts['username'], $this->opts['password'], $this->opts['db params']);
    /*
    foreach ($this->opts['db params'] as $key => $val)
      $this->db->setAttribute($key, $val);
    */
    
    return $this->qp;
  }
  
  /**
   * Execute a SQL query, and store the results.
   *
   * This will execute a SQL query (as a prepared statement), and then store
   * the results internally for later use. The data can be iterated using 
   * {@link nextRow()}. QueryPath can also be instructed to do internal iteration
   * using the {@link withEachRow()} method. Finally, on the occasion that the 
   * statement itself is needed, {@link getStatement()} can be used.
   *
   * Use this when you need to access the results of a query, or when the 
   * parameter to a query should be escaped. If the query takes no external 
   * parameters and does not return results, you may wish to use the 
   * (ever so slightly faster) {@link exec()} function instead.
   * 
   * Make sure you use {@link doneWithQuery()} after finishing with the database
   * results returned by this method.
   *
   * <b>Usage</b>
   * 
   * Here is a simple example:
   * <code>
   * <?php
   * QPQDB::baseDB($someDSN);
   * 
   * $args = array(':something' => 'myColumn');
   * qp()->query('SELECT :something FROM foo', $args)->doneWithQuery();
   * ?>
   * </code>
   *
   * The above would execute the given query, substituting myColumn in place of 
   * :something before executing the query The {@link doneWithQuery()} method
   * indicates that we are not going to use the results for anything. This method
   * discards the results.
   *
   * A more typical use of the query() function would involve inserting data
   * using {@link appendColumn()}, {@link prependColumn()}, {@link columnBefore()},
   * or {@link columnAfter()}. See the main documentation for {@link QPDB} to view
   * a more realistic example.
   * 
   * @param string $sql
   *  The query to be executed.
   * @param array $args
   *  An associative array of substitutions to make.
   * @throws PDOException
   *  Throws an exception if the query cannot be executed.
   */
  public function query($sql, $args = array()) {
    $this->stmt = $this->db->prepare($sql);
    $this->stmt->execute($args);
    return $this->qp;
  }
  
  /**
   * Query and append the results.
   *
   * Run a query and inject the results directly into the 
   * elements in the QueryPath object.
   *
   * If the third argument is empty, the data will be inserted directly into
   * the QueryPath elements unaltered. However, if a template is provided in 
   * the third parameter, the query data will be merged into that template
   * and then be added to each QueryPath element.
   *
   * The template will be merged once for each row, even if no row data is
   * appended into the template.
   *
   * A template is simply a piece of markup labeled for insertion of
   * data. See {@link QPTPL} and {@link QPTPL.php} for more information.
   *
   * Since this does not use a stanard {@link query()}, there is no need
   * to call {@link doneWithQuery()} after this method.
   *
   * @param string $sql
   *  The SQL query to execute. In this context, the query is typically a 
   *  SELECT statement.
   * @param array $args
   *  An array of arguments to be substituted into the query. See {@link query()}
   *  for details.
   * @param mixed $template
   *  A template into which query results will be merged prior to being appended
   *  into the QueryPath. For details on the template, see {@link QPTPL::tpl()}.
   * @see QPTPL.php
   * @see QPTPL::tpl()
   * @see query()
   */
  public function queryInto($sql, $args = array(), $template = NULL) {
    $stmt = $this->db->prepare($sql);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute($args);
    
    // If no template, put all values in together.
    if (empty($template)) {
      foreach ($stmt as $row) foreach ($row as $datum) $this->qp->append($datum);
    }
    // Otherwise, we run the results through a template, and then append.
    else {
      foreach ($stmt as $row) $this->qp->tpl($template, $row);
    }
    
    $stmt->closeCursor();
    return $this->qp;
  }
  
  /**
   * Free up resources when a query is no longer used.
   *
   * This function should <i>always</i> be called when the database
   * results for a query are no longer needed. This frees up the 
   * database cursor, discards the data, and resets resources for future
   * use. 
   *
   * If this method is not called, some PDO database drivers will not allow
   * subsequent queries, while others will keep tables in a locked state where
   * writes will not be allowed.
   *
   * @return QueryPath
   *  The QueryPath object.
   */
  public function doneWithQuery() {
    if (isset($this->stmt) && $this->stmt instanceof PDOStatement) {
      // Some drivers choke if results haven't been iterated.
      //while($this->stmt->fetch()) {}
      $this->stmt->closeCursor();
    }
      
    unset($this->stmt);
    $this->row = NULL;
    $this->cycleRows = FALSE;
    return $this->qp;
  }
  
  /**
   * Execute a SQL query, but expect no value.
   * 
   * If your SQL query will have parameters, you are encouraged to
   * use {@link query()}, which includes built-in SQL Injection 
   * protection.
   *
   * @param string $sql
   *  A SQL statement.
   * @throws PDOException 
   *  An exception will be thrown if a query cannot be executed.
   */
  public function exec($sql) {
    $this->db->exec($sql);
    return $this->qp;
  }
  
  /**
   * Advance the query results row cursor.
   *
   * In a result set where more than one row was returned, this will 
   * move the pointer to the next row in the set.
   *
   * The PDO library does not have a consistent way of determining how many
   * rows a result set has. The suggested technique is to first execute a
   * COUNT() SQL query and get the data from that.
   *
   * The {@link withEachRow()} method will begin at the next row after the
   * currently selected one.
   *
   * @return QueryPath
   *  The QueryPath object.
   */
  public function nextRow() {
    $this->row = $this->stmt->fetch(PDO::FETCH_ASSOC);
    return $this->qp;
  }
  
  /**
   * Set the object to use each row, instead of only one row.
   *
   * This is used primarily to instruct QPDB to iterate through all of the 
   * rows when appending, prepending, inserting before, or inserting after.
   *
   * @return QueryPath
   *  The QueryPath object.
   * @see appendColumn()
   * @see prependColumn()
   * @see columnBefore()
   * @see columnAfter()
   */
  public function withEachRow() {
    $this->cycleRows = TRUE;
    return $this->qp;
  }
  
  /**
   * This is the implementation behind the append/prepend and before/after methods.
   *
   * @param mixed $columnName
   *  The name of the column whose data should be added to the currently selected
   *  elements. This can be either a string or an array of strings.
   * @param string $qpFunc
   *  The name of the QueryPath function that should be executed to insert data
   *  into the object.
   * @param string $wrap
   *  The HTML/XML markup that will be used to wrap around the column data before
   *  the data is inserted into the QueryPath object.
   */
  protected function addData($columnName, $qpFunc = 'append', $wrap = NULL) {
    $columns = is_array($columnName) ? $columnName : array($columnName);
    $hasWrap = !empty($wrap);
    if ($this->cycleRows) {
      while (($row = $this->stmt->fetch(PDO::FETCH_ASSOC)) !== FALSE) {
        foreach ($columns as $col) {
          if (isset($row[$col])) {
            $data = $row[$col];
            if ($hasWrap) 
              $data = qp()->append($wrap)->deepest()->append($data)->top();
            $this->qp->$qpFunc($data);
          }
        }
      }
      $this->cycleRows = FALSE;
      $this->doneWithQuery();
    }
    else {
      if ($this->row !== FALSE) {
        foreach ($columns as $col) {
          if (isset($this->row[$col])) {
            $data = $this->row[$col];
            if ($hasWrap) 
              $data = qp()->append($wrap)->deepest()->append($data)->top();
            $this->qp->$qpFunc($data);
          }
        }
      }
    }
    return $this->qp;
  }
  
  /**
   * Get back the raw PDOStatement object after a {@link query()}.
   *
   * @return PDOStatement
   *  Return the PDO statement object. If this is called and no statement
   *  has been executed (or the statement has already been cleaned up),
   *  this will return NULL.
   */
  public function getStatement() {
    return $this->stmt;
  }
  
  /**
   * Get the last insert ID.
   *
   * This will only return a meaningful result when used after an INSERT.
   *
   * @return mixed
   *  Return the ID from the last insert. The value and behavior of this
   *  is database-dependent. See the official PDO driver documentation for
   *  the database you are using.
   * @since 1.3
   */
  public function getLastInsertID() {
    $con = self::$con;
    return $con->lastInsertId();
  }
  
  /**
   * Append the data in the given column(s) to the QueryPath.
   *
   * This appends data to every item in the current QueryPath. The data will
   * be retrieved from the database result, using $columnName as the key.
   *
   * @param mixed $columnName
   *  Either a string or an array of strings. The value(s) here should match 
   *  one or more column headers from the current SQL {@link query}'s results.
   * @param string $wrap
   *  IF this is supplied, then the value or values retrieved from the database
   *  will be wrapped in this HTML/XML before being inserted into the QueryPath.
   * @see QueryPath::wrap()
   * @see QueryPath::append()
   */
  public function appendColumn($columnName, $wrap = NULL) {
    return $this->addData($columnName, 'append', $wrap); 
  }
  
  /**
   * Prepend the data from the given column into the QueryPath.
   *
   * This takes the data from the given column(s) and inserts it into each
   * element currently found in the QueryPath.
   * @param mixed $columnName
   *  Either a string or an array of strings. The value(s) here should match 
   *  one or more column headers from the current SQL {@link query}'s results.
   * @param string $wrap
   *  IF this is supplied, then the value or values retrieved from the database
   *  will be wrapped in this HTML/XML before being inserted into the QueryPath.
   * @see QueryPath::wrap()
   * @see QueryPath::prepend()
   */
  public function prependColumn($columnName, $wrap = NULL) {
    return $this->addData($columnName, 'prepend', $wrap);
  }
  
  /**
   * Insert the data from the given column before each element in the QueryPath.
   *
   * This inserts the data before each element in the currently matched QueryPath.
   *
   * @param mixed $columnName
   *  Either a string or an array of strings. The value(s) here should match 
   *  one or more column headers from the current SQL {@link query}'s results.
   * @param string $wrap
   *  IF this is supplied, then the value or values retrieved from the database
   *  will be wrapped in this HTML/XML before being inserted into the QueryPath.
   * @see QueryPath::wrap()
   * @see QueryPath::before()
   * @see prependColumn()
   */
  public function columnBefore($columnName, $wrap = NULL) {
    return $this->addData($columnName, 'before', $wrap);
  }
  
  /**
   * Insert data from the given column(s) after each element in the QueryPath.
   *
   * This inserts data from the given columns after each element in the QueryPath
   * object. IF HTML/XML is given in the $wrap parameter, then the column data
   * will be wrapped in that markup before being inserted into the QueryPath.
   *
   * @param mixed $columnName
   *  Either a string or an array of strings. The value(s) here should match 
   *  one or more column headers from the current SQL {@link query}'s results.
   * @param string $wrap
   *  IF this is supplied, then the value or values retrieved from the database
   *  will be wrapped in this HTML/XML before being inserted into the QueryPath.
   * @see QueryPath::wrap()
   * @see QueryPath::after()
   * @see appendColumn()
   */
  public function columnAfter($columnName, $wrap = NULL) {
    return $this->addData($columnName, 'after', $wrap);
  }
  
}

// The define allows another class to extend this.
if (!defined('QPDB_OVERRIDE'))
  QueryPathExtensionRegistry::extend('QPDB');