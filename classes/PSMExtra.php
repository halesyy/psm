<?php
  class PSMExtra {
    # Some classes used in the query class rewrite to do other types of functions besides database interaction.
      # Usually used for text manipulation.

    public function quickparse($string) {
      # Key is replaced with value.
        $replaces = array(
          '(S*F)' => 'SELECT * FROM',
          '(S)' => 'SELECT',
          '(DF)' => 'DELETE FROM',
          '(D*F)' => 'DELETE * FROM',
          '(II)' => 'INSERT INTO',
          '(V)' => 'VALUES',
          '(W)' => 'WHERE',
          '(TRUN)' => 'TRUNCATE',
          '(UN)' => 'UNION',
          '(UNA)' => 'UNION ALL',
          '(|)' => 'OR',
          '(&)' => 'AND',
          '(ALT)' => 'ALTER',
          '(CRE)' => 'CREATE',
          '(DD)' => 'DROP DATABASE',
          '(EX)' => 'EXISTS',
          '(?)' => 'IF',
          '(OB)' => 'ORDER BY',
          '(DE)' => 'DESC',
          '(AS)' => 'ASC',
          '(UP)' => 'UPDATE'
        );
      # Looping and replacing string.
        foreach ($replaces as $search => $replace) {
          $string = str_replace($search, $replace, $string);
        }
      # Returning the string.
        return $string;
    }

      # Used to display array contents.
      public function display($arr) {
        echo '<pre>',print_r($arr),'</pre>';
      }
      /* Functions for class-based access. */
      public function implode($a, $b) {
        return implode($a, $b);
      }

  }
