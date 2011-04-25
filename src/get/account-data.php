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


// load list of developers
$developers = Enzyme::getDevelopers();
print_R($developers);
exit;


// check that account name is given and is valid
$account = trim($_REQUEST['account']);

if (empty($account) || !isset($developers[$account])) {
  App::returnHeaderJson(true, array('missing' => true));

} else {
  $accountData = $developers[$account];
  unset($developers);
}



// determine actions to take
if (!empty($_REQUEST['code']) && !empty($_REQUEST['new_password'])) {
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
  // generate and save access code to be sent via email


  // define email message
  $to       = array('name'    => $accountData['name'],
                    'address' => $accountData['email']);

  $message  = sprintf(_('A new application for %s has been made by %s (%s).'), $fields['job'], $fields['firstname'] . ' ' . $fields['lastname'], $fields['email']) . "\n";
              sprintf(_('Login at %s to decline or approve this application.'), ENZYME_URL);


  // send email
  $email            = new Email($to, sprintf('New Application at %s', PROJECT_NAME), $message);
  $json['success']  = $email->send();

//  // generate and store "change password" link
//  $user->data['reset_ip']       = $_SERVER['REMOTE_ADDR'];
//  $user->data['reset_code']     = App::randomString(20);
//  $user->data['reset_timeout']  = Date('Y-m-d H:i:s', strtotime('Now + 6 hours'));
//
//  $user->save();
//
//
//  // define email message
//  $to       = array('name'    => $user->getName(),
//                    'address' => $user->data['email']);
//
//  $message  = sprintf(_('%s, someone at the IP address %s has requested a password reset on your account.'), $user->data['firstname'], $user->data['reset_ip']) . "\n\n" .
//              sprintf(_('If you have requested the password reset, please go to %s'), BASE_URL . '/reset/' . $user->data['reset_code']) . "\n" .
//                      _('This link is valid for 6 hours, and one password change only.') . "\n\n" .
//                      _('Be sure to change your password immediately after logging in by going to "Settings" at the top right.') . "\n\n" .
//                      _('If you have not requested the password reset, please ignore this email.') . "\n" .
//              sprintf(_('If you get any more unrequested reset messages, please contact %s'), ADMIN_EMAIL) . "\n\n" .
//              sprintf(_('Thanks, the %s team'), PROJECT_NAME);
//
//
//  // send email
//  $email            = new Email($to, sprintf('%s Reset Password', PROJECT_NAME), $message);
//  $json['success']  = $email->send();
}








// return success
App::returnHeaderJson();

?>