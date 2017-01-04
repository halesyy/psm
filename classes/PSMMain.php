<?php
  class PSM extends PSMQuery  {
    /*
    | This is the main class for building your PSM object.
    |
    | PSM (PHP Scripting Module) is a simple PDO re-write aiming to make database
    | integration easier than easy.
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











  }
