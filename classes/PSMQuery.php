<?php
  class PSMQuery extends PSMExtra {
    /*
      Hello! And welcome to the PSM Query-based class.
      This class contains all of the query-based functions meant to replicate the PDO queries,
      So this is a bunch of functions you will soon become used to using!

      NOTE::
        Most of this used to be code that was commented, because of preference I have reverted to miniman amounts of comments,
        just cause it looks unprofessional, so, here's some basic terminologiy.

        $this->handler = The raw PDO object.

        If the function contains a:
          if ($binding) {

          } else {

          }
        That means there is an array known as a "binding" array, meant for prepared and executed statements, and it's telling if it should prepare / execute or just run the query.
        Also, most of my new functions have a lot of comments, cause when I make new things, I do lots of comments, makes it easier. :)
      ::
    */














    /*
      BASE PDO / TESTING FUNCTIONS.
    */
      # The testing function to see if PSM is working.
        public function test() {
          echo "<b>psm {$this->version} is open, and running fine.";
        } public function t() { return $this->test(); }

      /* This is a function making a simple query and looping the given function. */
        public function query($statement, callable $loop, $binding = false) {
          # THIS -> PDO HANDLER -> MAKE_QUERY_WITH -> $STATEMENT.
            if ($binding) { # There are binding variables, use!
              $query = $this->handler->prepare($statement);
              $query->execute($binding);
            } else { # No binding variables.
              $query = $this->handler->query($statement);
            }
          # Will feed the loop the PSM Variable, a $_POST object and a $_SESSION object.
            foreach ($query as $row) {
              $loop($this, (object) $_POST, (object) $_SESSION);
            }
          return $query;
        }
















      /* This static query get executed - This is a function that only needs to be ran, and not managed. */
        # - This functions can also be used as a query-return, just use this function to get the query back.
        public function static_query($statement, $binding = false) {
            if ($binding) {
              $query = $this->handler->prepare($statement);
              $query->execute($binding);
            } else {
              $query = $this->handler->query($statement);
            }
          return $query;
        }

      /* This function will return the first row gathered from a query. */
        public function query_set($statement, $binding = false) {
          # Using the now-made static_query function to get the returned true query back.
            $query = $this->static_query($statement, $binding);

            return $query->fetch(PDO::FETCH_ASSOC);
        }

      /* This function returns the row count of the query. */
        public function row_count($statement, $binding = false) {
          $query = $this->static_query($statement, $binding);

          return $query->rowCount();
        } public function rc($statement, $binding = false) { $this->row_count($statement, $binding); }
          # The sub-function to row_count meant to use a pre-made query instead of a statement.
          public function query_row_count($query) {
            return $query->rowCount();
          } public function qrc($statement, $binding = false) { $this->row_count($statement, $binding); }
          # End of sub-functions for row_count.

















    /*
      OBJECT-RELATED FUNCTIONS, OBJECT-FUNCTIONS AND PSM-RELATED FUNCTIONS.
        Functions that act on the handler directly.
    */

      /* Will get the PDO Handler connection. */
        public function connection() {
          return $this->handler;
        } public function conn() { return $this->handler; }
      /* Will close the current PDO handler connection. */
        public function close() {
          $this->handler = false;
        }
      /* Gets the last inserted ID. */
        public function glid() {
          return $this->handler->lastInsertId();
        } public function last_id() { return $this->glid(); }
      /* Returning the version of PSM. */
        public function version() {
          echo "<b>PHP version ".phpversion()."</b> - <b>PSM version {$this->version}</b>";
        } public function v() { $this->version(); }
      /* Dispalys information on the PSM class. */
        public function info($show_each_method = false) {
          $arr = []; # Return info.
          $methods = get_class_methods($this);
          $arr['amount of functions'] = count($methods);
          if ($show_each_method) {
            $arr['method names'] = $methods;
          }
          $arr['psm version'] = $this->version;
            $this->display($arr);
        }













    /*
      FUNCTIONS MEANT TO NOT TAKE IN RAW "STATEMENTS", AND INSTEAD TAKE SOME DATA AND MAKE IT.
    */
      /* The new update function - Will generate your update query and auto-bind it for you.
      The where part of the statement also generates a bind for you - just do "id = 1" and itll bind.  */
        public function update($table, $updates, $where = false, $debug = false) {
          # Starting the query.
            $statement = "UPDATE $table SET ";
          # Setting the cols / vals.
            $cols = array_keys($updates);
            $vals = array_values($updates);
          # Adds " = ?" too each of the cols values.
          foreach ($cols as $index => $col) { $cols[$index] = "$col = ?"; }
            $statement .= implode(', ', $cols);
          if ($where) {
            # Gets each side of the where statement given.
            $where_parts = explode(' = ',$where);
            $statement .= " WHERE {$where_parts[0]} = ?";
            array_push($vals, $where_parts[1]);
          }
          # Performing the query.
            $query = $this->handler->prepare($statement);
            $query->execute($vals);
          if ($debug) { echo $statement; $this->display($vals); }
        }

      /* Deletes all the ids that are given to it in the table given. */
        public function delete($table, $ids) {
          # Loops the given id - Deleting every id given.
            foreach ($ids as $id) {
              $query = $this->handler->prepare("DELETE FROM $table WHERE id = ?");
              $query->execute([$id]);
            }
        }
      /* Returns each column entry from the table asked, so if input = Table:users, col:username - An array is returned with each username. */
        public function getall($table, $col) {
          $query = $this->handler->query("SELECT $col FROM $table");
          $store = [];
            foreach ($query as $row) {
              array_push($store, $row[$col]);
            }
          return $store;
        } public function ga($table, $col) { $this->getall($table, $col); }

      /* Will go into the table and find the column, and find the ID of given ID, and increase it by how it wants. */
        public function plus($table, $col, $id, $by) {
            $set = $this->query_set("SELECT $col FROM $table WHERE id = :id",[
              ':id' => $id
            ]);
          # Getting the am the user has selected.
            $am = $set[$col];
            $new = $am + $by;
          # Going to insert the new value.
            $this->static_query("UPDATE $table SET $col = :new WHERE id = :id",[
              ':new' => $new,
              ':id' => $id
            ]);
        }

        # The vars we need for the following functions.
          private $currentquery = false;

        /* Function meant to save and set the current query into a PSM class variable. */
          public function savequery($statement, $binds = false) {
            # Setting the query and saving it.
              if ($binds) {
                $q = $this->handler->prepare($statement);
                $q->execute($binds);
              } else {
                $q = $this->handler->query($statement);
              }
            # Have the query, putting in the vars.
              $this->currentquery = $q;
          }

        /* Function that will return the the row count of the query, the query set, and the raw query.
        - Meant for if you're doing work that would normally require multiple lines to do. */
          public function data($statement, $binds) {
            # Saving the query to get the returned query in an object.
              $this->savequery($statement, $binds);
            # Getting all data needed for return.
              $q = $this->currentquery;
              $rowcount = $q->rowCount();
              $queryset = $q->fetch(PDO::FETCH_ASSOC);
            # Returning all the data gathered.
              return [
                'query' => $q,
                'rowcount' => $rowcount,
                'rc' => $rowcount,
                'set' => $queryset,
                'queryset' => $queryset
              ];
          }

        /* This is the new insert functions, with only the need for the table you want to insert into + an array like:
        column1 => data1,
        column2 => data2
        ALSO IT AUTO-BINDS YOUR QUERY! <3 . <3 */
          public function insert($table, $insert_array) {
            # Manip to form the query.
              $entries = count($insert_array);
            # Gets the columns and the vals that we are adding into.
              $cols = array_keys($insert_array);
              $vals = array_values($insert_array);
            # Setting the ?,? part of the query to be later inserted.
              $temp = '';
              for ($i = 1; $i <= $entries; $i++) {
                $temp .= '?,';
              } $temp = rtrim($temp,',');
              $binds = $temp;
              // $binds = str_replace(' ',',',$temp);
            # Constructing the query.
              $statement = "INSERT INTO $table ({$this->implode(',',$cols)}) VALUES ($binds)";
            # Preparing the query, then executing the prepare statement with the values from the given array.
              $query = $this->handler->prepare("INSERT INTO $table ({$this->implode(', ',$cols)}) VALUES ($binds)");
              $query->execute($vals);
            # The query was executed as wanted. - Hopefully lol... [this function took 2 hours to make cause of a stupid error]
          }

        /* This function is meant to take in data and check if the table data exists, if so do the function given in the array, if not then do the function in array if given. */
          public function if_entry_exists($array) {
            # Management for the info given.
              $info = $array['info'];
              // $this->display($info);
            # Will make the statement, will then construct the query and execute it.
              $statement = "SELECT * FROM {$info['table']} WHERE {$info['where']}";
              $q = $this->handler->prepare($statement);
              $q->execute($info['binds']);
                $rows = $q->rowCount();
            # Managing the functions and calling one needed.
              if ($rows) call_user_func($array['true'], $this, (object) $_POST, (object) $_SESSION);
                else call_user_func($array['false'], $this, (object) $_POST, (object) $_SESSION);
          }











      /*
        QUERY-BUILDER FUNCTIONS.
      */
        # Vars needed for the query-builder.
          public $statement = false;
          public $temp      = false;
          public $query     = false;

        # Function to check if a certain part of the query is there.
          public function requires($part) {
            # Splits the required parts into an array.
              $parts = explode(' ',$part);
              foreach ($parts as $required_temp) {
                # Checks if the wanted required_temp (each part of the array) exists in the array.
                if (empty($this->temp[$required_temp])) die('psm - query built in wrong order');
              }
          }
        # Selects the table.
          public function s($table) {
            $this->temp['table'] = $table;
            return $this;
          }
        # Selects the column.
          public function c($col) {
            # Requires makes sure that there is the temp[index] of what's given, so for this, there has to be a table existant.
            $this->requires('table');
            $this->temp['column'] = $col;
              $this->statement = "SELECT {$this->temp['column']} FROM {$this->temp['table']}";
            return $this;
          }
        # Selects the wanter where.
        # - We enforce using binding when using the query-builder, cause it's less messy.
          public function w($where) {
            if (count(explode('\'',$where)) != 1) die('psm - there is a quotation mark in <b>where</b> statement');
            if (count(explode(' = ',$where)) != 2) die('psm - query <b>where</b> not one equals');
            $this->requires('table column');
            $this->temp['where'] = $where;
              $this->statement .= " WHERE $where";
            return $this;
          }
        # The order-part of a query.
          public function o($order = 'd') {
            $asc_keywords = ['a','asc','ASC'];
            $dec_keywords = ['d','desc','DESC'];
            if (in_array($order,$asc_keywords)) {
              # Wants it to be order by asc.
              $this->temp['order'] - "ORDER BY id ASC";
            } else {
              # wants it to be order by desc.
              $this->temp['order'] = "ORDER BY id DESC";
            }
              $this->statement .= " {$this->temp['order']}";
            return $this;
          }
        # Print the current query.
          public function print_current_query($a = false, $b = false, $c = false) {
            echo $this->statement;
          }
        # Building the current query.
          public function build($binds) {
              $statement = $this->statement;
              $query = $this->handler->prepare($statement);
              $query->execute($binds);
            return $query;
          }
        # End of query-builder functions.

      /*
        MANAGEMENT FUNCTIONS.
      */
        /* Takes the "value" and checks if there is a post request,
        example "sub = login" = if (isset($_POST['sub']) AND $_POST['sub'] == 'login')
        VS "sub" = if (isset($_POST['sub']))*/
          public function post($value, callable $do) { global $psm;
            $values = explode(' = ',$value);
            # Example input: [1] sub = value [2] sub
            if (count($values) == 2) {
              # Ex value = [sub = value]
              $postName = $values[0];
              $postValu = $values[1];
              if (isset($_POST[$postName]) && $_POST[$postName] == $postValu) {
                # Will call the function "do" with the PSM Var and $_POST.
                $do($psm, (object) $_POST, $_POST);
              }
            } else {
              # Ex value = [sub]
              $postName = $values[0];
              if (isset($_POST[$postName])) {
                # Will call the fucntion "do" with the PSM Var and $_POST.
                $do($psm, (object) $_POST, $_POST);
              }
            }
          }
            # Will check all post requests. - and run through the "post" function. - POST MANAGEMENT.
            public function posts($functions) {
              foreach ($functions as $value => $function) {
                $this->post($value, $function);
              }
            }


          /* This is a function that is used practically and is a testing function as well. */
            # prepared, this is a function meant for a prepared and then executed statement.
            public function help($query = 'not-given') {
              if ($query == 'not-given') die('the function <b>help</b> is meant to supply you with information on the query you just tried to execute, and should be used if is failing.');
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
                if ($succ) {
                  echo 'The query executed correctly - There should be no need for the error report';
                } else {
                  $this->display($r);
                }
            } #
















      /*
        FUNCTIONS THAT LET YOU LOAD INTO PSM FROM ANOTHER DATABASE QUICKLY - MEANT TO QUICKLY LET YOU INTO YOUR DATABASE FOR EXAMPLE.

        This part of PSM is made for migrating databases. - Well the information, you just pass the "data_string" generated from save_details() and put it use:
          $conn = PSM::parse_data_string($YOUR_STRING_FROM_SAVE_DETAILS_FUNCTION);
            And you get an array containing either PSM or if you use Auth as well.
            Easy!
          This is a function meant for the Smallther PHP Framework. :)
          This will most-of-the-time not work for you unless you are using Smallther, will be updated later.
            This was now updated to have some-what normal PSM use.
      */
        /* This is the function you call if you want to save your details. */
          public function save_details() {
            global $authv, $use_auth, $use_database;
            # Making the data_string (adding the database information first)
            $data_string = "DB_{$this->db['host']}_{$this->db['database']}_{$this->db['username']}_{$this->db['password']}";
            # If the user is using the auth script, will get the details and add to the data_string.
            if ($use_auth) $data_string .= " AUTH_{$authv['key']}_{$authv['algotype']}_{$authv['salt']}_{$authv['tableName']}_{$authv['usernameTable']}_{$authv['passwordTable']}";

            return $data_string;
          }  public function data_string() { return $this->save_details(); }
        /* Function for parsing and returning the data_string given. */
          public static function parse_data_string($data_string) {
            if (count(explode(' ',$data_string)) == 2) {
              # String is to be parsed by DATABASE AUTH.
              $data_string = str_replace('DB_','',$data_string);
              $data_string = str_replace('AUTH_','',$data_string);
                $parts = explode(' ',$data_string);
              # We have the pieces of the database string as well as the auth pieces string.
              $db_pieces = explode('_', $parts[0]);
              $auth_pieces = explode('_', $parts[1]);
                $db = [
                  'host' => $db_pieces[0],
                  'db'   => $db_pieces[1],
                  'user' => $db_pieces[2],
                  'pass' => $db_pieces[3]
                ]; $auth = [
                  'key' => $auth_pieces[0],
                  'algotype' => $auth_pieces[1],
                  'salt' => $auth_pieces[2],

                  'tableName' => $auth_pieces[3],
                  'usernameTable' => $auth_pieces[4],
                  'passwordTable' => $auth_pieces[5]
                ];

                $generatedPsm = new PSM("{$db['host']} {$db['db']} {$db['user']} {$db['pass']}");
                $generatedAuth = new auth($generatedPsm);
                  $generatedAuth->setup($auth['algotype'],$auth['salt'],$auth['key']);
                  $generatedAuth->setupDatabase($auth['tableName'],$auth['usernameTable'],$auth['passwordTable']);
                # Returning the generated objects. (auth and psm)
                return [
                  'psm' => $generatedPsm,
                  'auth' => $generatedAuth
                ];
            } else { # FOR A DATABASE_ONLY RETURNED STRING FROM $PSM->SAFE_DETAILS() WHEN WAS USED.
              # String is to be parsed by DATABASE.
              $data_string = str_replace('DB_','',$data_string);
                $db_pieces = explode('_',$data_string);
              $db = [
                'host' => $db_pieces[0],
                'db'   => $db_pieces[1],
                'user' => $db_pieces[2],
                'pass' => $db_pieces[3]
              ];

              $generatedPsm = new PSM("{$db['host']} {$db['db']} {$db['user']} {$db['pass']}");
              # Returning the generated objects. (auth and psm)
              return [
                'psm' => $generatedPsm
              ];
            }
          } # End of da big ole function.































        /* JEKS TESTING AREA! :D */
          # There's currently nothing here! Looks like Jek's doing something else. :]














  } # End of the PSM Query class.









##
