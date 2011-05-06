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
  public $id          = 'data';
  public $title       = null;

  private $developer  = null;


  public function __construct() {
    // set title
    $this->title = _('Data');
  }


  public function draw() {
    // if code given, validate
    if (!empty($_REQUEST['code'])) {
      $this->developer = new Developer($_REQUEST['code'], 'access_code');

      if ($this->developer->data) {
        // draw data management interface
        return $this->drawData();

      } else {
        // invalid code
        return 'b';
      }

    } else {
      return $this->drawIntro();
    }
  }


  public function getScript() {
    return array('/js/frame/dataui.js');
  }


  public function getStyle() {
    return array('/css/dataui.css');
  }


  private function drawIntro() {
    $buf = '<h1>' . $this->title . '</h1>

            <p class="intro">' .
              sprintf(_('The %s features <a class="u_" href="%s">extended statistics</a>.'), PROJECT_NAME, BASE_URL . '/issues/latest/statistics/') . '<br />' .
              _('To generate exciting visualisations now and in the future, new information is needed from KDE contributors...') .
           '</p>

            <div class="row">
              <div class="left">' .
                _('Step 1:') .
           '  </div>
              <div class="right">' .
                _('Enter your SVN account name:') .

           '    <form id="account" method="post" action="">
                  <input id="account-name" type="text" name="name" />
                  <input id="account-send" type="button" value="Send!" onclick="accountData();" disabled="disabled" />
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


  private function drawData() {
    if (!$this->developer) {
      return false;
    }


    // get field/string mappings
    $fields = Developer::getFieldStrings();


    // draw fields
    $buf   = '<h1>' . $this->title . '</h1>

              <p class="intro">' .
                sprintf(_('This is the information used to represent you in the %s.'), PROJECT_NAME) . '<br />' .
                _('Please review and add/amend where appropriate.') . '<br />' .
                '<i>' . _('All fields are optional.') . '</i>' .
             '</p>

              <form id="data" method="post" action="">
                <table>
                  <tbody>';

    foreach ($fields as $id => $string) {
      $buf  .= '<tr>
                  <td class="title">' .
                    $string .
               '  </td>
                  <td class="value">' .
                    $this->drawField($id) .
               '  </td>
                </tr>';
    }

    $buf  .= '    </tbody>
                </table>
              </form>';

    return $buf;
  }


  private function drawField($key) {
    // display as special type, or using regular input element?
    if (isset(Developer::$fields[$key]) && (Developer::$fields[$key]['type'] == 'enum')) {
      return Ui::htmlSelector('data-' . $key,
                              Developer::enumToString('category', $key),
                              $this->developer->data[$key]);

    } else {
      // input
      if ($key == 'account') {
        $readonly = ' disabled="disabled"';
      } else {
        $readonly = null;
      }

      return '<input id="data-' . $key . '" type="text" value="' . $this->developer->data[$key] . '"' . $readonly . ' />';
    }
  }
}

?>