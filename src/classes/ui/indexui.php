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


class IndexUi extends Renderable {
  public $id            = 'index';
  public $title         = null;

  private $sixMonthsAgo = null;
  private $oneYearAgo   = null;
  private $random       = null;

  private $numIssues    = 5;


  public function __construct() {
    parent::__construct();

    // set title
    $this->title = _('Home');

    // load data
    $this->issues = Db::reindex(Cache::loadSave(array('base'  => DIGEST_APP_ID,
                                                      'id'    => 'issue_latest'),
                                                'Digest::loadDigests',
                                                array('issue',
                                                      'latest')),
                                                'date');

    // find 6 months ago, 1 year ago, random digests
    if ($this->issues) {
      $this->sixMonthsAgo = $this->issues[Digest::getLastIssueDate('6 months', true, true, true)];
      $this->oneYearAgo   = $this->issues[Digest::getLastIssueDate('1 year', true, true, true)];
      $this->random       = $this->issues[array_rand($this->issues)];
    }
  }


  public function draw() {
    $tokens = array(
      'issues'         => array_slice($this->issues, 0, 5),
      'six_months_ago' => $this->sixMonthsAgo,
      'one_year_ago'   => $this->oneYearAgo,
      'random'         => $this->random,
    );

    return parent::render($tokens);
  }


  public function getScript() {
    return array();
  }


  public function getStyle() {
    return array('/css/frame/indexui.css');
  }
}

?>