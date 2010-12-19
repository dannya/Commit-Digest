<?php

/*-------------------------------------------------------+
| KDE Commit-Digest
| Copyright 2010 Danny Allen <danny@commit-digest.org>
| http://www.commit-digest.org/
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/


include($_SERVER['DOCUMENT_ROOT'] . '/autoload.php');


// ensure needed params are set
if (empty($_REQUEST['date']) || !isset($_REQUEST['data'])) {
  App::returnHeaderJson(true, array('missing' => true));

} else {
  $date = date('Y-m-d', strtotime($_REQUEST['date']));

  // put values into suitable format for GET sending
  $data = str_replace(array('&', '='), array(';', ':'), $_REQUEST['data']);
}


// send off generate request
$request = file_get_contents(GENERATE_MAPS . '?type=map&regions=all&values=' . $data);


// decode map image locations
$request = json_decode($request);


// download images to local
$ch = curl_init();

foreach ($request->img as $image) {
  $ch = curl_init();

  // set output filename
  $filename = explode('/', $image);

  $base     = '/issues/' . $date . '/files/stats';
  $file     = '/standard-embedded_' . end($filename);
  $out      = BASE_DIR . $base . $file;

  // create parent directory for writing images into?
  if (!is_dir(BASE_DIR . $base)) {
    mkdir(BASE_DIR . $base, 0777, true);
  } else if (!is_writable(BASE_DIR . $base)) {
    //chmod(BASE_DIR . $base, 0777);
  }

  // set options
  curl_setopt($ch, CURLOPT_FILE, fopen($out, 'w'));
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_URL, $image);

  // download file
  curl_exec($ch);
  curl_close($ch);

  $json['img'][] = BASE_URL . $base . $file;
}


// send remote delete command
$delete = file_get_contents(GENERATE_MAPS . '?type=delete&dir=' . $filename[5]);


// return success
$json['success'] = true;

App::returnHeaderJson(true);

?>