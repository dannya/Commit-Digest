<?php

/*-------------------------------------------------------+
 | KDE Commit-Digest
 | Copyright 2010-2011 Danny Allen <danny@commit-digest.org>
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


// check access code is given
if (empty($_REQUEST['access_code'])) {
  App::returnHeaderJson(true, array('missing' => true));
}


// check access code is valid
$developer = new Developer($_REQUEST['access_code'], 'access_code');

if (!$developer->data) {
  App::returnHeaderJson(true, array('missing' => true));
}


// initialise
$date         = date('Y-m-d H:i:s');
$surveyData   = array();


// process project data
$i = 1;

while (!empty($_REQUEST['project-' . $i . '_name'])) {
  $surveyData[] = array('account' => $developer->data['account'],
                        'date'    => $date,
                        'section' => 'project',
                        'q'       => $i,
                        'string'  => $_REQUEST['project-' . $i . '_name'],
                        'a1'      => $_REQUEST['project-' . $i . '_1'],
                        'a2'      => $_REQUEST['project-' . $i . '_2'],
                        'a3'      => $_REQUEST['project-' . $i . '_3'],
                        'a4'      => $_REQUEST['project-' . $i . '_4'],
                        'a5'      => null);

  ++$i;
}


// process contributor data
$i = 1;

while (!empty($_REQUEST['contributor-' . $i . '_name'])) {
  $surveyData[] = array('account' => $developer->data['account'],
                        'date'    => $date,
                        'section' => 'contributor',
                        'q'       => $i,
                        'string'  => $_REQUEST['contributor-' . $i . '_name'],
                        'a1'      => $_REQUEST['contributor-' . $i . '_1'],
                        'a2'      => $_REQUEST['contributor-' . $i . '_2'],
                        'a3'      => $_REQUEST['contributor-' . $i . '_3'],
                        'a4'      => $_REQUEST['contributor-' . $i . '_4'],
                        'a5'      => $_REQUEST['contributor-' . $i . '_5']);

  ++$i;
}


// process motivation data
for ($i = 1; $i <= 18; $i++) {
  if (empty($_REQUEST['motivation-' . $i])) {
    continue;
  }

  $surveyData[] = array('account' => $developer->data['account'],
                        'date'    => $date,
                        'section' => 'motivation',
                        'q'       => $i,
                        'string'  => null,
                        'a1'      => $_REQUEST['motivation-' . $i],
                        'a2'      => null,
                        'a3'      => null,
                        'a4'      => null,
                        'a5'      => null);
}


// save survey data
$json['success'] = Db::saveMulti('developer_survey', $surveyData);


// return success
App::returnHeaderJson();

?>