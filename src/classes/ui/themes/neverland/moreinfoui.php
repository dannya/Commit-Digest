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


class MoreInfoUi {
  public $id            = 'moreinfo';
  public $title         = null;

  private $issue        = null;
  private $revision     = null;
  private $data         = null;
  private $type         = null;

  private $prevRevision = null;
  private $nextRevision = null;

  private $areas        = null;
  private $types        = null;


  public function __construct() {
    // set title
    $this->title = _('More Info');

    // extract revision number
    $this->issue     = trim($_REQUEST['date'], '/');
    $this->revision  = trim($_REQUEST['revision'], '/');

    // load digest containing revision
    $this->data = Digest::loadDigest($this->issue);

    // sanity check
    if (empty($this->data['commits']) || empty($this->revision)) {
      return false;
    }

    // set type (used in url)
    if ($this->data['type'] == 'archive') {
      $this->type = 'archive';
    } else {
      $this->type = 'issues';
    }


    // get previous / next revisions
    $revisions = array_keys($this->data['commits']);
    $key       = array_search($this->revision, $revisions);

    if (isset($revisions[$key - 1])) {
      $this->prevRevision = $revisions[$key - 1];
    }
    if (isset($revisions[$key + 1])) {
      $this->nextRevision = $revisions[$key + 1];
    }


    // extract revision details
    $this->data = $this->data['commits'][$this->revision];


    // get areas and types
    $this->areas = Enzyme::getAreas();
    $this->types = Enzyme::getTypes();
  }


  public function draw() {
    // sanity check
    if (empty($this->data['revision'])) {
      return false;
    }

    $buf = $this->drawTitleBox() .
           $this->drawDetails() .
           $this->drawDiffs();

    return $buf;
  }


  public function getScript() {
    return array();
  }


  public function getStyle() {
    return array('/css/frame/issueui' . MINIFIED . '.css');
  }


  private function drawTitleBox() {
    $prev        = null;
    $next        = null;

    // previous commit button?
    if ($this->prevRevision) {
      $prev = '<a href="' . BASE_URL . '/' . $this->type . '/' . $this->issue . '/moreinfo/' . $this->prevRevision . '/" title="' . sprintf(_('Go to revision %s'), $this->prevRevision) . '">' . _('Previous revision') . '</a>';
    }

    // next commit button?
    if ($this->nextRevision) {
      $next = '<a href="' . BASE_URL . '/' . $this->type . '/' . $this->issue . '/moreinfo/' . $this->nextRevision . '/" title="' . sprintf(_('Go to revision %s'), $this->nextRevision) . '">' . _('Next revision') . '</a>';
    }

    // shorten revision string if Git
    if (empty($this->data['format']) || ($this->data['format'] == 'svn')) {
      $revision = $this->data['revision'];

    } else if ($this->data['format'] == 'git') {
      $revision = Digest::getShortGitRevision($this->data['revision']);
    }

    // separator
    $separator = '';
    if ($prev && $next) {
      $separator = '<span class="separator">|</span>';
    }

    // draw box
    $buf = '<header class="alert alert-info">
                <h1>' . sprintf(_('Revision %s'), $revision) . '</h1>
                <span>
                    <a href="' . BASE_URL . '/issues/' . $this->issue . '/">' . sprintf(_('Go back to digest for %s'), Date::get('full', $this->issue)) . '</a>
                </span>

                <aside id="timewarp">' . $prev . $separator . $next . '</aside>
           </header>';

    return $buf;
  }


  private function drawDetails() {
    // draw title
    $type = array_slice($this->types, ($this->data['type'] - 1), 1);
    $area = array_slice($this->areas, ($this->data['area'] - 1), 1);

    $buf  = '<h1>' . sprintf(_('%s in %s'), reset($type), reset($area)) . '</h1>';

    // draw details
    $buf .= Digest::drawCommit($this->data, '2009', false);

    return $buf;
  }


  private function drawDiffs() {
    // safety check
    if (!isset($this->data['diff'])) {
      return '';
    }


    // compile into usable array
    foreach ($this->data['diff'] as $diff) {
      $diffs[$diff['operation']][] = $diff['path'];
    }


    // safety check
    if (count($diffs) === 0) {
      return '';
    }


    // setup subtitle strings
    $subtitles = array('A' => _('Added'),
                       'M' => _('Modified'),
                       'D' => _('Deleted'),
                       'I' => _('Ignored'),
                       'C' => _('Conflicted'));


    // draw title
    $buf = '<h1>' . _('File Changes') . '</h1>';


    // draw diffs
    $totalChanges = 0;

    $buf .= '<div class="diffs-container">';

    foreach ($diffs as $operation => $sectionDiffs) {
      // sort diffs
      usort($sectionDiffs, array('MoreInfoUi', 'sortDiff'));

      $total         = count($sectionDiffs);
      $totalChanges += $total;

      // check that section exists
      if (!isset($subtitles[$operation])) {
        continue;
      }

      // draw new section
      $buf .=  '<div class="subheader">' .
                  $subtitles[$operation] .
               '  <span>' .
                    sprintf(_('%d files'), $total) .
               '  </span>
                </div>

                <ul class="diffs">';

      // draw common base path?
      $basePath = Enzyme::getBasePath($sectionDiffs);

      if (strlen($basePath) > 0) {
        $buf .=  '  <li class="basepath">' .
                      $basePath .
                 '  </li>';
      }

      // draw diff (without basepath)
      foreach ($sectionDiffs as $diff) {
        if ($diff != $basePath) {
          $buf .=  '  <li class="path">
                        <span>&nbsp;</span> ' . str_replace($basePath, null, $diff) . '
                      </li>';
        }
      }

      $buf .= '</ul>';
    }


    // draw totals
    $buf .=  '  <div id="total" class="subheader">' .
                  sprintf(_('%d files changed in total'), $totalChanges) .
             '  </div>
              </div>';

    return $buf;
  }


  private static function sortDiff($a, $b) {
    $numA = substr_count($a, '/');
    $numB = substr_count($b, '/');

    if ($numA != $numB) {
      return $numA > $numB;
    } else {
      // same number of '/', compare alphabetically
      return strcasecmp($a, $b);
    }
  }
}

?>