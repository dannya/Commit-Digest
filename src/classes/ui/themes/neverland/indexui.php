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


class IndexUi {
  public $id            = 'index';
  public $title         = null;

  private $sixMonthsAgo = null;
  private $oneYearAgo   = null;
  private $random       = null;

  private $numIssues    = 5;


  public function __construct() {
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
    $buf =   '<div class="hero-unit">
                <h1>' . _('Welcome') . '</h1>
                <p class="lead">' . sprintf(_('...to the %s, a weekly overview of the development activity in KDE.'), Config::getSetting('enzyme', 'PROJECT_NAME')) . '</p>

                <div id="latest-box" class="filled" onclick="top.location=\'' . BASE_URL . '/issues/latest/\';">
                  <button class="btn btn-large btn-primary">' . _('Read the latest issue!') . '</button>
                </div>
              </div>
              <div class="row">
                <div class="span5">
                  <h2>' . _('Issues') . '</h2>
                  <ul id="issues">';

      $counter = 0;

      foreach ($this->issues as $digest) {
        // stop after $numIssues
        if ($counter++ == $this->numIssues) {
          break;
        }

        $buf .= $this->drawDigest($digest);
      }

      $buf .=  '  </ul>
                </div>

                <div class="span5">
                  <h2>' . _('Six Months Ago') . '</h2>
                  <ul>' .
                      $this->drawDigest($this->sixMonthsAgo, false) .
                 '</ul>

                  <h2>' . _('One Year Ago') . '</h2>
                  <ul>' .
                    $this->drawDigest($this->oneYearAgo, false) .
               '  </ul>

                  <h2>' . _('Random Digest') . '</h2>
                  <ul>' .
                    $this->drawDigest($this->random, false) .
               '  </ul>
                </div>
            </div>';

    return $buf;
  }


  public function getScript() {
    return array();
  }


  public function getStyle() {
    return array();
  }


  private function drawDigest($digest) {
    // process synopsis, removing HTML tags
    $synopsis = strip_tags($digest['synopsis']);

    $buf = '  <li>
                <h4>
                  ' . Date::get('full', $digest['date']) .
           '    </h4>
                <span>
                  <a class="text" href="' . BASE_URL . '/issues/' . $digest['date'] . '/" title="' . htmlspecialchars($synopsis) . '">' .
                    App::truncate($synopsis, 106, true) .
           '      </a>
                </span>
              </li>';

    return $buf;
  }
}

?>