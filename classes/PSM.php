<?php
  class PSM  {
    /*
    | This is the main class for building your PSM object.
    |
    | PSM (PHP Scripting Module) is a simple PDO re-write aiming to make database
    | integration easier than easy.
    | This is the "whole class" file, used for a single import of your PSM client.
    | The other files are contained in
    | ./PSMExtra.php, ./PSMQuery.php & ./PSMMain.php
    */

    // ************************************************************************

      public $usingdrivers    = false;
      public $connected       = false;
      public $safeconnection  = true;
      public $handler         = false;
      protected $db           = [];

      public $version = "3.0.0 - <i>NOT FOR USE IN PRODUCTION</i>";

    // ************************************************************************


      public function __construct($connection_string = false, $options = false)
        {
          if ($connection_string !== false)
          $this->connect( $connection_string, $options );
        }

      public function connect($connectionVars = 'localhost test root EM', $options = false)
        {
          // Option manager.
          if ($options !== false) {
            if (isset($options['safeconnection'])) {
              $this->usingdrivers = true;
                $this->safeconnection = $options['safeconnection'];
            }
          }

          // Managing the connection variables.
          if (!is_array($connectionVars)) {
            $exploded = explode(' ',$connectionVars);
            $host = $exploded[0]; $database = $exploded[1]; $username = $exploded[2]; $password = $exploded[3];
          } else {
            $host = $connectionVars['host'];
            $database = $connectionVars['dbname'];
            $username = $connectionVars['username'];
            $password = $connectionVars['password'];
          }
          $password = str_replace('EM','',$password);

          try {
            $db = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
            $this->connected = true;
            $this->handler   = $db;
            $this->db = [
              'host' => $host,
              'database' => $database,
              'username' => $username,
              'password' => (empty($password)) ? 'EM' : $password
            ];
            return $db;
          }  catch(PDOException $e) {
            echo "<b>PHP Scripting Module {$this->version}</b> connection error. <i>(details given failed to connect)</i>";
            if ($this->safeconnection === false)
              echo "<p>Unsafe connecton trace:</p>"
              ."<pre>\n"
              ."host-name: {$host}\ndb-name..: {$database}\nusername.: {$username}\npassword.: {$password}"
              ."</pre>";
            die();
          }
        }


      // Function if you're going to store PSM in a SESSION or SERIALIZE.
      // When pulling from the variable, use $psm->open(); to load in the database again.
      public function close()
        {
          $this->handler = false;
        }
      public function open()
        {
          $this->connect( "{$this->db['host']} {$this->db['database']} {$this->db['username']} {$this->db['password']}" );
        }


    /*
    | -------------------------------------------------------------------
    | BEGINNING OF THE QUERY CLASS FROM THE QUERY FILE.
    | -------------------------------------------------------------------
    */



    /*
    * @category  PHP Database Management
    * @package   PSM - PHP Scripting Module
    * @author    Jack Hales <halesyy@gmail.com, jek@intuor.net>
    * @copyright 2001 - 2016 Jack Hales
    * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
    * @version   GIT: <174549a75a080a24279ffcb2608b5cf27b4adf8a>
    * @link      http://jekoder.com, http://intuor.net - Not used often.

    | This is the PSM extended class for queries that PSM can perform.
    | Contains functions meant to re-write and auto-bind PDO functions.
    */

    // ****************************************************************************

      /*All PDO accessor maps.*/
      const assoc = PDO::FETCH_ASSOC;
      const obj   = PDO::FETCH_OBJ;
      const lazy  = PDO::FETCH_LAZY;
      const named = PDO::FETCH_NAMED;
      const both  = PDO::FETCH_BOTH;
      const clas  = PDO::FETCH_CLASS;

      /*Temporary PSM variables.*/
      public $tempstatement = '';
      public $tempbinds     = [];
      public $tempquery     = false;

    // ****************************************************************************


      /*Function called if you want to test your PSM/PDO connection. */
        public function test()
          {
            echo "<b>psm {$this->version} is open, and running fine.";
          } public function t() { return $this->test(); }



      /*
      | Analyzes the query as well as housing for the error handler.
      | Error handler will report data on query if needed.
      */



      /*QA = Query Analyzer, will analyze a query and report back.*/
        public function QA($query = 'ng-false', $ret = false)
          {
            if ($query === 'ng-false') die('the function <b>help</b> is meant to supply you with information on the query you just tried to execute, and should be used if is failing.');
            # Getting the error info array.
              $e = $query->errorInfo();
              $r = []; # R = Return, returning array.
            # Will start adding values into the Return array.
              $r['PSM - pHelp'] = '<b>INFORMATION GATHERED FROM THE PREPARED / EXECUTED QUERY</b>';
              $r['MYSQL eCode'] = $e[0].' - Google the meaning of the code';
              $r['Drivr eCode'] = $e[1].'  - Error specific to the driver';
            # Manip for getting the correct error message to display.
              $search = [
                '/Table \'(.*?)\.(.*?)\' doesn\'t exist/is',
                '/You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near (.*?) at line (.*?)/is',
                '/Unknown column \'(.*?)\' in \'(.*?)\'/is',
                '/Column count doesn\'t match value count at row (.*?)/is'
              ]; $replace = [
                'The table <b>$2</b> doesn\'t exist in the database <b>$1</b>, did you spell it right?',
                'There was a syntax error, if you want to combat it effectively, please seperate each SQL word into seperate lines then retry, now check <b>line: $2</b>',
                'The column (<b>$1</b>) in the table you tried to insert/select from into doesn\'t exist, did you spell it right?',
                'The amount of <b>column\'s</b> vs <b>inserting values</b> does not match up, re count them!'
              ]; $r['Custom Mesg'] =  preg_replace($search,$replace,$e[2]);
              $r['Raw Message'] = $e[2];
              if ($query->execute()) $r['Query Execu'] = 'true';
                else $r['Query Execu'] = 'false';
            # Management for checking if there is even an error.
              if ($e[0] == "00000") $succ = true;
                else $succ = false;

            # Return management.
              if ($succ && $ret) return "query_no_error";
              else if ($succ && !$ret)
                echo 'The query executed correctly - There should be no need for the error report';
              else
                {
                  $this->Display($r);
                  $this->Display($query);
                }
          } public function Help($query = 'not-given', $ret = false) { $this->QA($query, $ret); }



      /*Will create a query.*/
        public function cquery($statement, $binding = false)
          {
            if ($binding !== false)
              {
                $q = $this->handler->prepare($statement);
                $return = $q->execute($binding);
                if ($return === false) $this->Error("<strong>CQUERY</strong> called", "Execution of query returned false - Showing query data from display", $q, true);
                else return $q;
              }
            else
              {
                $q = $this->handler->query($statement);
                if ($q === false) $this->Error("<strong>CQUERY</strong called", "Query build returned fail - Showing query data from display", $q, false);
                else return $q;
              }
          }



      /*Will create a statement depending on the inputs.*/
        public function cstatement($array, $build = false)
          {
            // Reset definitions.
            if (isset($array['columns'])) $array['cols'] = $array['columns'];
            // Creating the statement container as well as the binds contains.
            $statement = 'SELECT';
            $binds     = [];
            // COLUMNS.
            if (isset( $array['cols'] )) $statement .= " {$array['cols']}";
            else $statement .= ' *';
            // TABLE.
            if (isset( $array['table'] )) $statement .= " FROM {$array['table']}";
            // WHERE.
            if (isset( $array['where'] ))
              {
                $where_clauses = explode(' = ', $array['where']);
                $statement .= " WHERE {$where_clauses[0]} = ?";
                array_push($binds, $where_clauses[1]);
              }
            // ORDER.
            if (isset( $array['order'] )) $statement .= " ORDER BY {$array['order'][0]} {$array['order'][1]}";
            // LIMIT.
            if (isset( $array['limit'] )) $statement .= " LIMIT {$array['limit']}";
            // RETURN.
            if ($build)
              {
                $q = $this->cquery( $statement, $binds );
                return $q;
              }
            else
              {
                // Manager for returning data.
                if (count($binds) == 0)
                  {
                    // Sets the temporary statement for later retrieval if needed.
                    $this->tempstatement = $statement;
                    return $statement;
                  }
                else
                  {
                    // Sets the temporary binds / statement for later retrieval if needed.
                    $this->tempbinds     = $binds;
                    $this->tempstatement = $statement;
                    return [
                      'statement' => $statement,
                      'binds'     => $binds
                    ];
                  }
              }
          }
      /*The short-hand function for the 'cstatement' function.*/
        public function short_cstatement($array, $build = false)
          {
            // Array definitions are: [Table, Cols, Where, Order and Limit].

            if (isset($array[0])) $call['table'] = $array[0];
            if (isset($array[1])) $call['cols']  = $array[1];
            if (isset($array[2])) $call['where'] = $array[2];
            if (isset($array[3])) $call['order'] = $array[3];
            if (isset($array[4])) $call['limit'] = $array[4];

            return $this->cstatement( $call, $build );
          }



      /*Does a static query -- i.e. INSERT, etc...*/
        public function stat($statement, $binding)
          {
            $this->cquery( $statement, $binding );
          }


      /* Will do a loop with the query data. */
        public function loop($statement, callable $loop, $binding = false, $fetch_type = PSM::assoc )
          {
            $query = $this->cquery( $statement, $binding );
            // Will loop the query data.
            foreach ( $query->fetchAll( $fetch_type ) as $row )
              $loop( $row, $this );
            return $query;
          }



      /*
      | More PSM-core functions to aid in sole-manipulation.
      */


      /*Returns the first set given from the query data.*/
        public function set( $statement, $binding = false )
          {
            $query = $this->cquery($statement, $binding);
            return $query->fetch(PDO::FETCH_ASSOC);
          }

      /*Gets the row count of the statement.*/
        public function rows($statement, $binding = false)
          {
            $rows = $this->cquery($statement, $binding)->rowCount();
            return $rows;
          } public function hasdata($statement, $binding = false) { if ($this->rows($statement, $binding)) return true; else return false; }
            public function hasnodata($statement, $binding = false) { if ($this->rows($statement, $binding)) return false; else return true; }

      /* This function is meant to take in data and check if the table data exists, if so do the function given in the array, if not then do the function in array if given.
      A more vigarous version of the "hasdata()" function.*/
        public function hasdata_specific($table, $column, $columnequals, $callers = false)
          {
            // Will construct statement: SELECT column FROM table WHERE column = :columndata
            $statement = "SELECT {$column} FROM {$table} WHERE {$column} = ?";
            $binds     = [$columnequals];
            // Return.
            // No array supplied for callbacks.
            if ($callers == false)
              if ($this->rows( $statement, $binds )) return true;
              else return false;
            else
            // Array supplied for callbacks.
              if ($this->rows( $statement, $binds ))
                call_user_func( $callers['true'], $this );
              else
                call_user_func( $callers['false'], $this );
          }
      /*Meant to return the row count of the query given, for query creation then multiple usage.*/
        public function query_rows($query)
          {
            $rows = $query->rowCount();
            return $rows;
          }


      /*
      | PSM-Related functions as well as some PDO method rewrites like LastInsertID, etc...
      */

        // Returns PDO connection.
        public function connection()
          {
            return $this->handler;
          }

        // Returns true/false if connected.
        public function connected()
          {
            return $this->connected;
          }

        // Returns the IP of the client.
        public function ip()
          {
            return $_SERVER['REMOTE_ADDR'];
          }

        // Returns the last inserted ID.
        public function last()
          {
            return $this->handler->LastInsertID();
          } public function glid() { $this->last(); }

        // Displays the PSM version.
        public function version($return = false)
          {
            $v = "<b>PHP version ".phpversion()."</b> - <b>PSM version {$this->version}</b>";
            if (phpversion() == '5.6.21') $v .= "<br/><b>PHP VERSION + PSM VERSION = MOST TESTED</b>";
            if ($return) return $v; else echo $v;
          } public function v($return = false) { $this->version($return); }

        // Displays information on the PSM class.
        public function info($show_each_method = false)
          {
            $arr = [];
            $methods = get_class_methods($this);
            $arr['amount of functions'] = count($methods);
            if ($show_each_method)
              $arr['method names'] = $methods;
            $arr['psm version'] = $this->version(true);
            $this->display($arr);
          }

      // Returns the columns of a table.
      public function get_cols($table)
        {
          $q = $this->cquery( "SELECT * FROM {$table} LIMIT 1" );
          $cols = array_keys( $q->fetch(PDO::FETCH_ASSOC) );
          return $cols;
        }

      // Returns true or false for if a table is existant.
      public function table_exists($table)
        {
          if ( $this->hasdata("SHOW TABLES LIKE :table", [':table' => $table]) )
            return true; else return false;
        }



    /*
    | Functions meant to make calls simpler to the database - and safer.
    | You get sick of writing long SQL statements, so use these shorthand functions to
    | treat array keys as columns and values as data.
    */



      /*Makes the update function easier to use.*/
        public function update($table, $updates, $where, $debug = false)
          {
            // Build the start of the query.
              $statement = "UPDATE {$table} SET ";
            // Getting the columns and values.
              $cols = array_keys($updates);
              $vals = array_values($updates);
            // Add ' = ?' to each of the columns.
              foreach ($cols as $index => $col) { $cols[$index] = "$col = ?"; }
              $statement .= implode(', ', $cols);
            // Splits to create the sides of the where statement.
              $where_parts = explode(' = ',$where);
              $statement .= " WHERE {$where_parts[0]} = ?";
              array_push($vals, $where_parts[1]);
            // Performing the query.
              $q = $this->cquery( $statement, $vals );
            // Debug manager.
              if ($debug) { $this->Display($vals); echo $statement; }
          }



      /* Deletes all the ids that are given to it in the table given. */
        public function delete($table, $col = false, $selectors = false)
          {
            if ($col === false)
              {
                // Delete table.
                $this->cquery("DROP TABLE {$table}");
              }
            else
              {
                // Delete what the user asks for.
                /*
                | Essentially, COL = the column to look for, and SELECTORS = the data to look for.
                | Example: col = id, selectors = [1, 2, 3], DELETE FROM table WHERE id = 1,2,3
                */
                $statement = "DELETE FROM {$table} WHERE {$col} IN (";
                $a_selectors = [];
                foreach ($selectors as $selector) array_push($a_selectors, '?');
                $statement .= implode(',', $a_selectors);
                $statement .= ')';
                $binds = $selectors;
                // Execute.
                $this->cquery( $statement, $binds );
              }
          }



      /* Returns each column entry from the table asked, so if input = Table:users, col:username - An array is returned with each username. */
        public function get_column_data($table, $col)
          {
            $q = $this->cquery( "SELECT {$col} FROM {$table}" );
            $store = [];
              foreach ($q->fetchAll() as $row)
              array_push($store, $row[$col]);
            return $store;
          }



      /* Will go into the table and find the column, and find the ID of given ID, and increase it by how it wants. */
        public function plus($table, $selector_array)
          {
            // Set.
            $col = $selector_array['col'];
            $id  = $selector_array['id'];
            if (isset($selector_array['by'])) $by = $selector_array['by']; else $by = 1;
            // Retrieval.
            $set = $this->cquery("SELECT {$col} FROM {$table} WHERE id = :id ORDER BY id DESC LIMIT 1", [
              ':id' => $id
            ])->fetch();
            // Update.
            $this->update($table, [
              "{$col}" => $set[$col] + $by
            ], "id = {$id}", true);
          }



      /* Function that will return the the row count of the query, the query set, and the raw query.
      - Meant for if you're doing work that would normally require multiple lines to do. */
        public function data($statement, $binds)
          {
            $q = $this->cquery($statement, $binds);

            $set  = $q->fetch(PDO::FETCH_ASSOC);
            $rows = $q->rowCount();

            $ret = [
              'query'     => $q,
              'rowcount'  => $rows,
              'queryset'  => (object) $set,
              'set'       => (object) $set,
              'statement' => $statement,
              'binds'     => $binds
            ];
            $ret = (object) $ret;
            return $ret;
          }



      /* This is the new insert functions, with only the need for the table you want to insert into + an array like:
      column1 => data1,
      column2 => data2
      ALSO IT AUTO-BINDS YOUR QUERY! <3 . <3 */
        public function insert($table, $insert_array, $debug = false)
          {
            // Columns being inserted into and their datasets.
            $cols       = array_keys(   $insert_array );
            $binds      = array_values( $insert_array );
            // Building the array containining the shadow-binds: [?,?,?] turns into (?,?,?).
            $binder_builder = [];
            for ($i = 1; $i < count($insert_array); $i++)
            array_push($binder_builder, '?');
            // Builds the statement and executes.
            $statement = "INSERT INTO {$table} ({$this->implode(', ',$cols)}) VALUES ({$this->implode(',',$binder_builder)})";
            $q = $this->cquery($statement, $binds);
            // Debug management.
            if ($debug) { $this->Display($binds); echo $statement; }
          }



      /*Function meant to encapsuleate the PSM state for creation on an external server.*/
      public function capsule()
        {
          $capsule  = "PSM_DB_CAPSULE{";
            $capsule .= "{$this->db['host']}::";
            $capsule .= "{$this->db['database']}::";
            $capsule .= "{$this->db['username']}::";
            $capsule .= "{$this->db['password']}";
          $capsule .= "}";
          return $capsule;
        }



      /*Will convert the capsule into a PSM variable.*/
      public function decapsule($string)
        {
          // Doing a simple resolve to make sure it's a PSM_DB_CAPSULE.
          $pieces = explode('{',$string);
          if ($pieces[0] != 'PSM_DB_CAPSULE') $psm->Error('<b>Decapsule()</b> called', 'Capsule type not = <b>PSM_DB_CAPSULE</b>');

          // Managing the data given from the capsule.
          $capsule_data = $pieces[1];
          $capsule_data = rtrim($capsule_data,'}');

          $split_capdata = explode('::', $capsule_data);
          if (count($split_capdata) != 4) $psm->Error('<b>Decapsule()</b> called', 'Capsule split data not = <b>4</b> pieces of string data');
          $cstring = "{$split_capdata[0]} {$split_capdata[1]} {$split_capdata[2]} {$split_capdata[3]}";

          $this->connect($cstring);
        }



      /*
      | Chains returning to PSM.
      | Chains let you link methods together then execute the callback function given.
      | Use for SELECT functions only.
      */

      // Initial SELECT statement to get the selection accessor.
      public function select($selection_array)
        {
          $this->tempstatement = '';
          $this->tempbinds     = [];

          if (!is_array($selection_array))
            $selection_array = [$selection_array];
          if (count($selection_array) > 2) $this->Error('<b>Select()</b> chain called', 'Input Array > 2 entries');
          if (count($selection_array) == 2)
            $this->tempstatement = "SELECT {$selection_array[1]} FROM {$selection_array[0]}";
          else
            $this->tempstatement = "SELECT * FROM {$selection_array[0]}";
          return $this;
        }



      // The where clause statement constructor.
      public function where($clause, $raw = false, $binds = false)
        {
          if ($raw)
            {
              // Raw WHERE statement supplied.
              $this->tempstatement .= $raw;
              array_push($this->tempbinds, $binds);
            }
          else
            {
              // Easy manip WHERE statement supplied.
              $where_clauses = explode(' ', $clause);
              if (count($where_clauses) != 3) $psm->Error('<b>Where()</b> chain called', 'Not 2 spaces in statement, if using a special WHERE statement, please set 2nd param to \'true\' for raw input.');
              $this->tempstatement .= " WHERE {$where_clauses[0]} {$where_clauses[1]} ?";
              array_push($this->tempbinds, $where_clauses[2]);
            }
          return $this;
        }



      // Limits the row amount.
      public function limit($index, $selectamm = false)
        {
          if (!$selectamm)
            {
              // The user just wants to limit the amount of rows from index.
              $this->tempstatement .= " LIMIT {$index}";
            }
          else
            {
              // The user wants to specify the index then select amount.
              $this->tempstatement .= " LIMIT {$index},{$selectamm}";
            }
          return $this;
        }



      // The order clause manager.
      public function order($order_keyword, $col = false)
        {
          if ($col === false) $col = 'id';
          $order_keyword = strtoupper($order_keyword);
          $ascending     = ['^','up','a','asc'];
          $descending    = ['v','down','d','desc'];

          if (in_array($order_keyword, $ascending))
          $this->tempstatement .= " ORDER BY {$col} ASC";
          else $this->tempstatement .= "ORDER BY {$col} DESC";

          return $this;
        }



      // Loops the wanted tempstatement and tempbinds.
      public function run(callable $looper, $debug = false)
        {
          if ($debug)
            {
              echo "<p><b>DEBUG data (kills script after display): [statement&binds]</b></p>";
              echo "<p>{$this->tempstatement}</p>";
              $this->display($this->tempbinds);
              exit;
            }

          if (count($this->tempbinds) == 0)
            // No binding of query.
            $q = $this->cquery( $this->tempstatement );
          else
            // Binding of query.
            $q = $this->cquery( $this->tempstatement, $this->tempbinds );
          foreach ($q->fetchAll() as $row)
          $looper($row);
        }



      // Shorthand function for chaining the statement builder.
      public function chainer($chainer_array, callable $looper)
        {
          if (isset($chainer_array[0])) $this->select($chainer_array[0]);
          if (isset($chainer_array[1])) $this->where($chainer_array[1]);
          if (isset($chainer_array[2])) $this->order($chainer_array[2]);
          if (isset($chainer_array[3])) $this->limit($chainer_array[3 ]);
          $this->run($looper);
        }


      /*
      | -------------------------------------------------------------------
      | START OF THE EXTRA CLASS METHODS.
      | -------------------------------------------------------------------
      */


      /*
      | QPARSE IS AN OLD FUNCTION, COMPARISION FUNCTIONS NOW:
      |   $statement = $psm->qparse("(S*F) users (W) username = :un");
      |   $statement = $psm->cstatement(['users','username','username = :un']);
      | Might be longer, but faster alternative + easier to read syntax.
      */

      public function quickparse($string)
      {
        $replaces = array(
          '(S*F)'  => 'SELECT * FROM',
          '(S)'    => 'SELECT',
          '(DF)'   => 'DELETE FROM',
          '(D*F)'  => 'DELETE * FROM',
          '(II)'   => 'INSERT INTO',
          '(V)'    => 'VALUES',
          '(W)'    => 'WHERE',
          '(TRUN)' => 'TRUNCATE',
          '(UN)'   => 'UNION',
          '(UNA)'  => 'UNION ALL',
          '(|)'    => 'OR',
          '(&)'    => 'AND',
          '(ALT)'  => 'ALTER',
          '(CRE)'  => 'CREATE',
          '(DD)'   => 'DROP DATABASE',
          '(EX)'   => 'EXISTS',
          '(?)'    => 'IF',
          '(OB)'   => 'ORDER BY',
          '(DE)'   => 'DESC',
          '(AS)'   => 'ASC',
          '(UP)'   => 'UPDATE'
        );
        foreach ($replaces as $search => $replace)
        $string = str_replace($search, $replace, $string);
        return $string;
      }
      public function qparse($string)
      {
        return $this->quickparse($string);
      }
      public function Error($title, $message, $query = 'no-query', $is_prepared = true)
      {
        echo "<div class='psm-error'><div class='psm-error-title'>{$title}</div><div class='psm-error-message'>{$message}</div></div>";
        $this->display($query);
        $this->display(['is-prepared' => $is_prepared]);
        exit;
      }
      public function display($arr)
      {
        echo '<pre>',print_r($arr),'</pre>';
      }
      public function implode($a, $b)
      {
        return implode($a, $b);
      }
      public function explode($a, $b)
      {
        return explode($a, $b);
      }
  }


  /*
  | -------------------------------------------------------------------
  | START OF THE NEW CLASS FOR AUTH, RENAME IF CONFLICTING.
  | -------------------------------------------------------------------
  */


  class Auth
  {
    /*
    | NOTE NOT AFFILIATED WITH THAT PSM SCUM!
    | ----------------------------------------------------------------------
    | This is a test Joint to show the possibilities, with this Joint
    | you are able to hash passwords and manage if they're correct or not.
    | MAKE SURE TO SALT! (Don't just pepper!)
    |               .:::.
    |     .:::.     /:::::\
    |    /:':':\   |  _  |
    |    |  _  |   | (_` |
    |    | |_) |   | ,_) |
    |    | |   |   |     |
    |    |     |  /`'---'`\
    |    /`'---'`\ `'-----'`
    |    `'-----'`
    | ----------------------------------------------------------------------
    */

    //**********************************************************************

      protected $algorithm = false;
      protected $salt      = false;
      protected $set       = false;

      protected $psm       = false;
      protected   $table   = 'users';
      protected   $first   = 'username';
      protected   $secnd   = 'password';

    //**********************************************************************

    /*Setting algorithm & salt we're using. Salt is not set so people
    don't end up not using a salt.*/
    public function __construct( $algorithm = 'gost', $salt, $psm, $database = [] )
      {
        $this->algorithm = $algorithm;
        $this->salt      = $salt;
        //To let the class know you've set your vars.
        $this->set       = true;

        //Managing the Database work.
        $this->psm = $psm;
        $this->table = $database['table'];
        $this->first = $database['first'];
        $this->secnd = $database['secnd'];
      }

    /*Hashes the string.*/
    public function Hash($string)
      {
        $salted = $this->Salt( $string );
        $hashed = hash( $this->algorithm, $salted );

        return $hashed;
      }

    /*Adds salt to string.*/
    private function Salt($string)
      {
        $salt = $this->salt;
        return $salt.$salt.$string.$salt.$salt;
      }

    /*Checks if details are correct from first & second.*/
    public function correct( $first, $second )
      {
        $data = $this->psm->data(
          "SELECT id FROM {$this->table} WHERE {$this->first} = :fir AND {$this->secnd} = :snd",
          [':fir' => $first, ':snd' => $second]
        );
      }
  }
