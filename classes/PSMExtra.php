<?php
  class PSMExtra {
    /*
    | QPARSE IS AN OLD FUNCTION, COMPARISION FUNCTIONS NOW:
    |   $statement = $psm->qparse("(S*F) users (W) username = :un");
    |   $statement = $psm->cstatement(['users','username','username = :un']);
    | Might be longer, but faster alternative + easier to read syntax.
    */

    public function quickparse($string)
    {
      $replaces = array(
        '(S*F)'  => 'SELECT * FROM',
        '(S)'    => 'SELECT',
        '(DF)'   => 'DELETE FROM',
        '(D*F)'  => 'DELETE * FROM',
        '(II)'   => 'INSERT INTO',
        '(V)'    => 'VALUES',
        '(W)'    => 'WHERE',
        '(TRUN)' => 'TRUNCATE',
        '(UN)'   => 'UNION',
        '(UNA)'  => 'UNION ALL',
        '(|)'    => 'OR',
        '(&)'    => 'AND',
        '(ALT)'  => 'ALTER',
        '(CRE)'  => 'CREATE',
        '(DD)'   => 'DROP DATABASE',
        '(EX)'   => 'EXISTS',
        '(?)'    => 'IF',
        '(OB)'   => 'ORDER BY',
        '(DE)'   => 'DESC',
        '(AS)'   => 'ASC',
        '(UP)'   => 'UPDATE'
      );
      foreach ($replaces as $search => $replace)
      $string = str_replace($search, $replace, $string);
      return $string;
    }
    public function qparse($string)
    {
      return $this->quickparse($string);
    }
    public function Error($title, $message, $query = 'no-query', $is_prepared = true)
    {
      echo "<div class='psm-error'><div class='psm-error-title'>{$title}</div><div class='psm-error-message'>{$message}</div></div>";
      $this->display($query);
      $this->display(['is-prepared' => $is_prepared]);
      exit;
    }
    public function display($arr)
    {
      echo '<pre>',print_r($arr),'</pre>';
    }
    public function implode($a, $b)
    {
      return implode($a, $b);
    }
    public function explode($a, $b)
    {
      return explode($a, $b);
    }
  }
