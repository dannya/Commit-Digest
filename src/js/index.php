<?php

include($_SERVER['DOCUMENT_ROOT'] . '/autoload.php');

$validScript =  array('common');


// determine the script file to include
if (isset($_GET['script']) && in_array($_GET['script'], $validScript)) {
  header('Content-type: application/javascript');

  include_once(BASE_DIR . '/js/includes/' . $_GET['script'] . '.php');
}

?>