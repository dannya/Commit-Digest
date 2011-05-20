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


class CommitSpyUi {
  public $id        = 'commit-spy';
  public $title     = null;

  private $commits  = null;


  public function __construct() {
    // set title
    $this->title = _('Commit Spy');
  }


  public function draw() {
    // define dropdowns
    $filters   = array(''        => _('(type)'),
                       'account' => _('Account'),
                       'path'    => _('Path'),
                       'message' => _('Message'));

    $intervals = array('1'   => '1',
                       '2'   => '2',
                       '5'   => '5',
                       '10'  => '10');

    $buf = '<h1 id="frame-title">' . $this->title . '</h1>
            <img id="spinner" src="' . BASE_URL . '/img/spinner.gif" alt="" />

            <div id="options">
              <input id="filter" type="text" value="' . _('filter?') . '" class="prompt" onfocus="inputPrompt(event);" onblur="inputPrompt(event); forceUpdateSpy();" /> ' .
              Ui::htmlSelector('filter-type', $filters, null, 'forceUpdateSpy();') . '&nbsp;&nbsp;|&nbsp;&nbsp;' .
              sprintf(_('Update every %s minutes'),
                      Ui::htmlSelector('update-interval', $intervals, '5')) .
           '</div>

            <div id="recent-commits">
            </div>';

    // draw share / donate box
    $theUrl         = BASE_URL . '/commit-spy/';
    $theTitle       = 'KDE Commit Digest - Commit Spy';
    $theDescription = 'Watch commits across KDE in real-time.';

    $buf .= DigestUi::drawShareBox($theUrl, $theTitle, $theDescription);

    return $buf;
  }


  public function getScript() {
    return array('/js/frame/commitspyui.js');
  }


  public function getStyle() {
    return array('/css/issueui.css', '/css/commitspyui.css');
  }


  public function drawRecentCommits() {
    // get commits
    $this->commits = Cache::load('recent-commits', true);

    if (!$this->commits) {
      $this->commits = file_get_contents(RECENT_COMMITS);

      // store in cache for 60 seconds to prevent hammering
      Cache::save('recent-commits', $this->commits, true, 60);
    }

    $this->commits = simplexml_load_string($this->commits);


    // set timezone offset
    $systemOffset = date('Z');

    if (isset($_REQUEST['timeDiff'])) {
      $userOffset = App::flipNumber($_REQUEST['timeDiff']) * 60;
    } else {
      $userOffset = $systemOffset;
    }


    // extract data
    $commits = array();

    foreach ($this->commits->channel->item as $item) {
      $theDetails    = explode('(', $item->description);
      $theDetails[0] = explode(' ', $theDetails[0]);
      if (isset($theDetails[1])) {
        $theDetails[1] = explode(' ', rtrim($theDetails[1], '):'));
      }

      $commit['developer']  = $theDetails[0][0];
      $commit['revision']   = ltrim($theDetails[0][2], 'r');
      $commit['basepath']   = '/trunk/KDE/' . rtrim(ltrim($theDetails[0][3], '/'), ':');
      $commit['timestamp']  = strtotime($item->pubDate) - $systemOffset;
      $commit['date']       = date('jS F Y @ H:i:s', $commit['timestamp'] + $userOffset);
      $commit['msg']        = $item->title;

      // filter?
      if (!empty($_REQUEST['filter_type']) && !empty($_REQUEST['filter']) && ($_REQUEST['filter'] != _('filter?'))) {
        if ( (($_REQUEST['filter_type'] == 'account')  && (stripos($commit['developer'], $_REQUEST['filter']) === false)) ||
             (($_REQUEST['filter_type'] == 'path')     && (stripos($commit['basepath'], $_REQUEST['filter']) === false)) ||
             (($_REQUEST['filter_type'] == 'message')  && (stripos($commit['msg'], $_REQUEST['filter']) === false)) ) {

          // commit doesn't match filter, skip
          continue;
        }
      }

      // add to processed collection
      $commits[] = $commit;
      unset($commit);
    }


    // load developers
    if ($commits) {
      $developers = Enzyme::getDevelopers($commits);

      // draw commits
      $buf = null;

      foreach ($commits as $commit) {
        if (isset($developers[$commit['developer']]['name'])) {
          $theDeveloper = $developers[$commit['developer']]['name'];
        } else {
          $theDeveloper = $commit['developer'];
        }

        // set basepath

        $buf .=  '<div class="commit">
                    <span class="intro">' .
                      sprintf(_('%s (%s) committed changes in %s:'),
                      '<a class="n" href="http://cia.vc/stats/author/' . $commit['developer'] . '/">' . $theDeveloper . '</a>',
                      $commit['developer'],
                      Enzyme::drawBasePath($commit['basepath'])) .
                 '  </span>

                    <div class="details">
                      <p class="msg">' .
                        $commit['msg'] .
                 '    </p>

                      <div class="info">
                        <span class="date">' .
                          $commit['date'] .
                 '      </span>

                        <span class="timestamp" rel="' . $commit['timestamp'] . '">
                        </span>

                        <span class="revision">' .
                          sprintf(_('Revision %s'), $commit['revision']) .
                 '      </span>
                      </div>
                    </div>
                  </div>';
      }

    } else {
      // no commits found
      if (!empty($_REQUEST['filter_type']) && !empty($_REQUEST['filter']) && ($_REQUEST['filter'] != _('filter?'))) {
        // filter probably too restrictive
        $prompt = _('No commits found, maybe your filter is too restrictive...');
      } else {
        $prompt = _('No commits found, maybe your filter is too restrictive...');
      }

      $buf = '<div class="prompt">' .
                $prompt .
             '</div>';
    }

    return $buf;
  }
}

?>