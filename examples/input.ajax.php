<?php

var_dump($_POST);

if($_POST) {
  foreach($_POST as $k=>$v) {
    echo "$k = $v<br>";
  }
}
