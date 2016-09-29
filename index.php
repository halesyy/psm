<?php
  # Using session_start() cause PSM will sometimes supply the $_SESSION variable to you :D
  session_start();
    require('classes/PSMExtra.php');
    require('classes/PSMQuery.php');
    require('classes/PSMMain.php');

    $psm = new PSM('localhost test root EM',[
      'safeconnection' => true
    ]);

    $psm->query("SELECT * FROM users WHERE id = :id", function($row, $psm, $p, $s){
      echo "my name is {$row->username}";
    }, [':id' => 3], PSM::lazy);
