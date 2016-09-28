<?php
  require_once('classes/PSMExtra.php');
  require_once('classes/PSMQuery.php');
  require_once('classes/PSMMain.php');

  $psm = new PSM('localhost test root EM',[
    'safeconnection' => true
  ]);

  $psm->update('users',[
    'username' => 'jek_fucking_loves_salami',
    'password' => 'FAMAMFMAMFMA',
    'time' => time(),
    'ip' => $_SERVER['REMOTE_ADDR']
  ]);
