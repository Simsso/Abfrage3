<?php
  echo stripslashes($_GET['a']) . '<br>';
  echo var_dump(json_decode(stripslashes($_GET['a'])), true);
?>