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


class DataTermsUi {
  public $id        = 'data-terms';
  public $title     = null;

  public $noFrame   = false;

  private $terms    = null;
  private $version  = null;
  private $error    = false;


  public function __construct() {
    // set title
    $this->title = _('Data Usage Terms');

    // hide frame?
    if (isset($_REQUEST['noFrame'])) {
      $this->noFrame = true;
    }

    // load specific version of terms, or latest terms if not specified
    if (!empty($_REQUEST['version'])) {
      $this->version = rtrim(trim($_REQUEST['version']), '/');
    } else {
      $this->version = DATA_TERMS_VERSION;
    }

    if (!$this->terms = Db::load('data_terms', array('version' => $this->version), 1)) {
      // terms could not be loaded, note this and load latest version
      $this->error  = true;
      $this->terms  = Db::load('data_terms', array('version' => DATA_TERMS_VERSION), 1);
    }
  }


  public function draw() {
    $buf   = '<div id="terms">
                <h1>' .
                  $this->title .
             '    <i>' . sprintf(_('Version %2.1f'), $this->version) . '</i>
                </h1>';

    if ($this->error) {
      // terms could not be loaded, draw prominent message informing user at top of latest terms
      $buf  .= '<p>' .
                  sprintf(_('Version %2.1f of the data usage terms could not be found'),
                          $this->version) .
               '</p>';
    }


    // draw terms
    $buf  .=   $this->drawTerms() .
             '</div>';


    return $buf;
  }


  private function drawTerms() {
    $buf   = null;

    if (!empty($this->terms['parse'])) {
      $terms = explode("\n", $this->terms['content']);

      foreach ($terms as $item) {
        $item = trim($item);

        if (!empty($item)) {
          $buf  .= '<p>' . $item . '</p>';
        }
      }

    } else {
      // just output given text/html
      $buf  .= $this->terms['content'];
    }

    return $buf;
  }


  public function getScript() {
    return array();
  }


  public function getStyle() {
    return array();
  }
}

?>