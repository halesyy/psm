<?php
  require_once('classes/PSMExtra.php');
  require_once('classes/PSMQuery.php');
  require_once('classes/PSMMain.php');

  $psm = new PSM('localhost test root EM',[
    'safeconnection' => true
  ]);
