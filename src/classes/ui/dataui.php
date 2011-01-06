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


class DataUi {
  public $id      = 'data';
  public $title   = null;


  public function __construct() {
    // set title
    $this->title = _('Data');
  }


  public function draw() {
    $buf = '<h1>' . $this->title . '</h1>

            <tr>
              <td class="content_body" align="left">
                The KDE Commit-Digest features <a class="u_" href="/issues/latest/statistics/">extended statistics</a>.<br />To generate exciting visualisations now and in the future, new information is needed from KDE contributors...<br /><br />
              </td>
            </tr>

            <div class="row">
              <div class="left">' .
                _('Step 1:') .
           '  </div>
              <div class="right">' .
                _('Enter your SVN account name:') .

           '    <form id="account" method="post" action="">
                  <input type="text" name="name" />
                  <input type="button" value="Send!" onclick="alert(\'Coming soon!\');" />
                </form>' .

                _('An email will then be sent from this domain to the email address your SVN account is linked to.') .
           '    <br />
                <i>' . _('(Please wait up to 48 hours for this email)') . '</i>
              </div>
            </div>

            <div class="row">
              <div class="left">' .
                _('Step 2:') .
           '  </div>
              <div class="right">' .
                _('Reply to the email, following the instructions carefully.') .
           '  </div>
            </div>

            <div class="row">
              <div class="left">' .
                _('Step 3:') .
           '  </div>
              <div class="right">
                <i>' . _('Thanks!') . '</i>
                <br />' .
                _('You will soon be represented in the extended statistics.') .
           '  </div>
            </div>

            <p class="terms">' .
              _('I promise to keep your information confidentially, and to only use the data collected for KDE statistics purposes, unless further permission is granted.') .
           '</p>';

    return $buf;
  }


  public function getScript() {
    return array();
  }


  public function getStyle() {
    return array('/css/dataui.css');
  }
}

?>