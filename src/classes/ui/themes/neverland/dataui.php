<?php

/*-------------------------------------------------------+
 | KDE Commit-Digest
 | Copyright 2010-2013 Danny Allen <danny@commit-digest.org>
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
      $this->developer = new Developer($_REQUEST['code'], 'access_code', true);

      if ($this->developer->data) {
        // draw data management interface
        return $this->drawData();

      } else {
        // invalid code
        return 'Invalid access code';
      }

    } else {
      return $this->drawIntro();
    }
  }


  public function getScript() {
    return array('/js/frame/dataui' . MINIFIED . '.js');
  }


  public function getStyle() {
    return array('/css/frame/dataui' . MINIFIED . '.css');
  }


  private function drawIntro() {
    $buf = '<h1>' . $this->title . '</h1>

            <p class="intro">' .
              sprintf(_('The %s features <a class="u_" href="%s">extended statistics</a>.'), Config::getSetting('enzyme', 'PROJECT_NAME'), BASE_URL . '/issues/latest/statistics/') . '<br />' .
              _('To generate exciting visualisations now and in the future, new information is needed from KDE contributors...') .
           '</p>

            <div id="step_1">
              <div class="left">' .
                _('Step 1:') .
           '  </div>
              <div class="right">' .
                _('Enter your developer account name:') .

           '    <form id="account" method="post" action="">
                  <input id="account-name" type="text" name="name" />
                  <input id="account-send" type="button" value="' . _('Send!') . '" onclick="accountData();" disabled="disabled" />
                </form>

                <p>' .
                  _('An email will then be sent from this domain to the email address your developer account is linked to.') .
           '    </p>
                <p>' .
                  _('Please allow up to 30 minutes for this email to be sent.') .
           '    </p>
              </div>
            </div>

            <div id="step_2">
              <div class="left">' .
                _('Step 2:') .
           '  </div>
              <div class="right">
                <div id="step_2-before">' .
                  _('Reply to the email, following the instructions carefully.') .
           '    </div>
                <div id="step_2-after" style="display:none;">' .
                  _('Visit the link contained in the email.') . '<br />' .
                  _('Alternatively, enter the access code from the email here:') . '<br />
                  <input id="step_2-code" type="text" class="success" value="" />
                </div>
              </div>
            </div>

            <div id="step_3">
              <div class="left">' .
                _('Step 3:') .
           '  </div>
              <div class="right">
                <i>' . _('Thanks!') . '</i>
                <br />' .
                _('You will soon be represented in the extended statistics.') .
           '  </div>
            </div>';

    return $buf;
  }


  private function drawData() {
    if (!$this->developer) {
      return false;
    }


    // define section titles
    $titles = array('core'        => _('Core'),
                    'geographic'  => _('Geographic'),
                    'social'      => _('Social'));


    // get field/string mappings
    $fields = Developer::getFieldStrings();


    // draw fields
    $buf   = '<h1>' .
                $this->title . '
                <i>' .
                  sprintf(_('This data is held under <a href="%s" target="_blank">version %2.1f of the data usage terms</a>'),
                          BASE_URL . '/data/terms/' . $this->developer->privacy['terms_accepted'],
                          $this->developer->privacy['terms_accepted']) .
             '  </i>
              </h1>

              <p class="intro">' .
                sprintf(_('This is the information used to represent you in the %s.'), Config::getSetting('enzyme', 'PROJECT_NAME')) . '<br />' .
                _('Please review and add/change details where appropriate.') .
             '</p>

              <form id="data" method="post" action="">';

    foreach (Developer::$fieldSections as $section => $sectionFields) {
      $str = null;

      foreach ($sectionFields as $id) {
        // show this field?
        if (Developer::$fields[$id]['display'] != 'all') {
          continue;
        }

        // set data-privacy field
        if (is_string(Developer::$fields[$id]['privacy'])) {
          $privacy = ' data-privacy="' . Developer::$fields[$id]['privacy'] . '"';
        } else if (is_array(Developer::$fields[$id]['privacy'])) {
          $privacy = ' data-privacy="' . reset(Developer::$fields[$id]['privacy']) . '"';
        } else {
          $privacy = null;
        }

        // draw row
        $str  .= '<tr class="' . $this->getPrivacyClass($id) . '" data-field="' . $id . '"' . $privacy . '>
                    <td class="title">' .
                      $fields[$id] .
                 '  </td>
                    <td class="value">' .
                      $this->drawField($id) .
                 '  </td>
                    <td class="privacy">' .
                      $this->drawPrivacy($id) .
                 '  </td>
                  </tr>';
      }

      // only draw section if we have at least one row!
      if ($str) {
        $buf  .= '<h3>' .
                    $titles[$section] .
                 '</h3>

                  <table class="data">
                    <tbody>' .
                      $str .
                 '  </tbody>
                  </table>';
      }
    }

    // add access code into form
    $buf  .= '  <input id="access_code" name="access_code" type="hidden" value="' . $this->developer->access['code'] . '" />';

    // form buttons
    if ($this->developer->privacy['terms_accepted'] != Config::getSetting('enzyme', 'DATA_TERMS_VERSION')) {
      $dataTermsAlert  = '<label id="terms_accepted_container">
                            <input id="terms_accepted" type="checkbox" value="1" />' .
                            sprintf(_('I allow this data to be used under <a href="%s" target="_blank">version %2.1f of the data usage terms</a>'),
                                    BASE_URL . '/data/terms/' . Config::getSetting('enzyme', 'DATA_TERMS_VERSION'),
                                    Config::getSetting('enzyme', 'DATA_TERMS_VERSION')) .
                         '</label>';
    } else {
      $dataTermsAlert  = null;
    }

    $buf  .= '  <div class="buttons">
                  <input type="submit" value="' . ('Save') . '" onclick="save(event);" />
                  <img id="spinner" src="' . BASE_URL . '/img/spinner.gif" alt="" style="display:none;" />' .
                  $dataTermsAlert .
             '  </div>
              </form>';

    return $buf;
  }


  private function getPrivacyClass($key) {
    if (!empty(Developer::$fields[$key]['privacy'])) {
      if (($this->developer->privacy[$key] === true) || ($this->developer->privacy[$key] === 1)) {
        return 'privacy-private';

      } else {
        // anything else is public (or at least to some degree if enum-based)
        return 'privacy-public';
      }

    } else {
      return 'privacy-always';
    }
  }


  private function drawField($key) {
    // display as special type, or using regular input element?
    if (isset(Developer::$fields[$key]) && (Developer::$fields[$key]['type'] == 'enum')) {
      // add onchange function?
      if ($key == 'continent') {
        $onchange = 'changeContinent();';
      } else {
        $onchange = null;
      }

      return Ui::htmlSelector('data-' . $key,
                              Developer::enumToString('category', $key, true),
                              $this->developer->data[$key],
                              $onchange, null, null, true);

    } else {
      // input
      if ($key == 'account') {
        $readonly = ' disabled="disabled"';
      } else {
        $readonly = null;
      }

      return '<input id="data-' . $key . '" name="data-' . $key . '" type="text" value="' . $this->developer->data[$key] . '"' . $readonly . ' />';
    }
  }


  private function drawPrivacy($key) {
    if (($key == 'latitude') || ($key == 'longitude') || ($key == 'microblog_user')) {
      return null;
    }

    // draw
    if (!empty(Developer::$fields[$key]['privacy'])) {
      if ($key == 'dob') {
        // enum-based privacy
        $enum = array(1           => _('Nothing'),
                      'age'       => _('Age (Years)'),
                      'birthday'  => _('Birthday (Month and day)'),
                      0           => _('Birthdate (Day/Month/Year)'));

        // draw radio buttons
        $buf = '<b>' . _('Currently public:') . '</b>';

        foreach ($enum as $level => $string) {
          if ($level === $this->developer->privacy[reset(Developer::$fields[$key]['privacy'])]) {
            $checked = ' checked="checked"';
          } else {
            $checked = null;
          }

          $buf  .= '<label>
                      <input id="privacy_' . $key . '_' . $level . '" type="radio" name="privacy_' . $key . '" value="' . $level . '" onchange="changePrivacy(event);"' . $checked . ' />
                      <span>' . $string . '</span>
                    </label>';
        }

      } else {
        // checkbox
        if ((bool)$this->developer->privacy[$key]) {
          $checked  = ' checked="checked"';
          $string   = _('This field is currently <b>private</b>');

        } else {
          $checked = null;
          $string   = _('This field is currently <b>public</b>');
        }

        $buf = '<label>
                  <input id="privacy_' . $key . '" type="checkbox" value="1" onchange="changePrivacy(event);"' . $checked . ' />
                  <span>' . $string . '</span>
                </label>';
      }

    } else {
      // privacy cannot be modified by user
      $buf = _('This field is <b>always public</b>');
    }

    return $buf;
  }
}

?>