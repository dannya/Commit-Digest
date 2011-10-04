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


class OptionsUi {
  public $id      = 'options';
  public $title   = null;


  public function __construct() {
    // set title
    $this->title = _('Options');
  }


  public function draw() {
    $buf = '<h1>' . $this->title . '</h1>

            <p>' . _('Coming soon!') . '</p>';

    return $buf;
  }


  public function getScript() {
    return array();
  }


  public function getStyle() {
    return array('/css/frame/optionsui.css');
  }
}

?>