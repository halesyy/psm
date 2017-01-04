<?php
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
