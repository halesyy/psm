<?php
  class PSM extends PSMQuery  {
    #
      # Setting vars for if we're using drivers, if we're connected, if we want a safe connection, and the handler variable for the PDO handler.
        public $usingdrivers    = false;
        public $connected       = false;
        public $safeconnection  = true;
        public $handler         = false;
        protected $db           = [];
      # The current version of PSM that we're running.
        public $version = "2.0.0";
      # The init function to make the PDO connection and manage the data given.
        public function __construct($connectionVars = 'localhost test root EM', $options = false) {
          # Connecting to a database.
            # Option management.
            if ($options !== false) {
              if (isset($options['safeconnection'])) {
                $this->usingdrivers = true;
                  $this->safeconnection = $options['safeconnection'];
              }
            }
            # Setting each variable needed.
            if (!is_array($connectionVars)) { # If string isn't an array.
              $exploded = explode(' ',$connectionVars);
                # Setting the variables - host,database,username,password.
                $host = $exploded[0]; $database = $exploded[1]; $username = $exploded[2]; $password = $exploded[3];
            } else { # Is an array.
                # Setting the variables - host,database,username,password.
                $host = $connectionVars['host']; $database = $connectionVars['dbname']; $username = $connectionVars['username']; $password = $connectionVars['password'];
            }
            # Management for variables.
              $password = str_replace('EM','',$password);
            # Attempting to make  aconnection to the database.
            try {
              $db = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
              # Setting class variables.
              $this->connected = true;
              $this->handler   = $db;
              # Adding the database connection values.
              $this->db = [
                'host' => $host,
                'database' => $database,
                'username' => $username,
                'password' => (empty($password)) ? 'EM' : $password
              ];
              return $db;
            }  catch(PDOException $e) {
              echo "<b>PHP Scripting Module {$this->version}</b> connection error. <i>(details given failed to connect)</i>";
              # Management for wanting a non-safe connection.
                if ($this->safeconnection === false) {
                  echo "<br/><i>Unsafe connection trace: <br/>";
                    echo "
<font style='font-family: Monospace, Arial;'><pre>
  host-name: $host
  db-name..: $database
  username.: $username
  password.: $password

    <b>You sure that's the right password?</b>
    <b>You sure the server connects to that domain?</b>
</pre></font>";
                  # Kills the script.

                }
                die();
            }
        } # End of constructing the query magic function.














  }
