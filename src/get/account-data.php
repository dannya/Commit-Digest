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


// initialise
$json['success'] = false;


// determine action to take
if (!empty($_REQUEST['account'])) {
  // check that the account name is valid (by attempting to load into $account)
  $filter = array('account' => trim($_REQUEST['account']));

  if (!($account = Db::load('developers', $filter, 1))) {
    App::returnHeaderJson(true, array('invalid' => true));
  }


  // generate and save access code to be sent via email
  $data                     = array();
  $data['access_ip']        = $_SERVER['REMOTE_ADDR'];
  $data['access_code']      = App::randomString(20);
  $data['access_timeout']   = Date('Y-m-d H:i:s', strtotime('Now + 6 hours'));

  $success = Db::save('developer_privacy', $filter, $data);

  if ($success) {
    // define email message
    $to       = array('name'    => $account['name'],
                      'address' => $account['email']);

    $message  = sprintf(_('%s, someone at the IP address %s has requested to view/modify the identifying information used to represent you in the %s.'), $account['name'], $data['access_ip'], PROJECT_NAME) . "\n\n" .
                sprintf(_('If you have made this request, please go to %s'), BASE_URL . '/data/' . $data['access_code']) . "\n" .
                        _('This link is valid for 6 hours.') . "\n\n" .
                        _('If you have not made this request, please ignore this email.') . "\n" .
                sprintf(_('If you get any more unrequested messages, please contact %s'), ADMIN_EMAIL) . "\n\n" .
                sprintf(_('Thanks, the %s team'), PROJECT_NAME);

    // send email
//    $email            = new Email($to, sprintf('%s Information Access Request', PROJECT_NAME), $message);
//    $json['success']  = $email->send();
    $json['success'] = true;

  } else {
    $json['success'] = $success;
  }


} else if (!empty($_REQUEST['access_code'])) {
  print_R($_REQUEST);
  exit;

//  // check code is valid
//  if (($user->data['reset_code'] != $_REQUEST['code']) ||
//      (time() > strtotime($user->data['reset_timeout']))) {
//
//    App::returnHeaderJson(true, array('success' => false));
//  }
//
//  // change password
//  $user->data['password']       = $user->getHash(trim($_REQUEST['new_password']));
//
//  // unset reset details
//  $user->data['reset_ip']       = null;
//  $user->data['reset_code']     = null;
//  $user->data['reset_timeout']  = null;
//
//  // save details
//  $json['success'] = $user->save();


} else {
  // needed parameter is missing
  App::returnHeaderJson(true, array('missing' => true));
}


// return success
App::returnHeaderJson();

?>