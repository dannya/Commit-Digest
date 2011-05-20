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


class StatisticsUi {
  public $id            = 'statistics';
  public $title         = null;

  private $ui           = null;


  public function __construct($current, $issue) {
    // set title
    $this->title = _('Statistics');

    // initialise issue UI
    $current  = trim($_REQUEST['type'], '/');
    $issue    = trim($_REQUEST['date'], '/');;

    $this->ui = new IssueUI($current, $issue);
  }


  public function draw() {
    // draw digest issue title box
    $buf = $this->ui->drawTitleBox('statistics');

    // draw statistics
    $buf .= $this->ui->container('stats', $this->ui->drawStatistics());

    return $buf;
  }


  public function getScript() {
    return array('/js/plotr.js',
                 '/js/frame/issueui.js');
  }


  public function getStyle() {
    return array('/css/issueui.css');
  }
}

?>