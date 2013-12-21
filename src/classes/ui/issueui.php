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


class IssueUi {
  public $id            = null;
  public $title         = null;
  public $issue         = null;
  public $review        = false;

  private $data         = null;

  private $prevIssue    = null;
  private $nextIssue    = null;

  private $prevIssueUrl = null;
  private $nextIssueUrl = null;

  private $showUrl      = false;

  private $areas        = array();
  private $types        = array();


  public function __construct($id, $issue) {
    // set id
    $this->id = $id;


    // review?
    if (isset($_GET['review'])) {
      $this->review = true;
    }


    // load list of issues
    if ($this->review) {
      // if review, look at all issues
      if ($this->id == 'issues') {
        $issues = Cache::loadSave(array('base'  => DIGEST_APP_ID,
                                        'id'    => 'issue_latest_unpublished'),
                                  'Digest::loadDigests',
                                  array('issue',
                                        'latest',
                                        false));

      } else if ($this->id == 'archive') {
        $issues = Cache::loadSave(array('base'  => DIGEST_APP_ID,
                                        'id'    => 'archive_latest_unpublished'),
                                  'Digest::loadDigests',
                                  array('archive',
                                        'latest',
                                        false));
      }

    } else {
      // if not review, only look at published issues
      if ($this->id == 'issues') {
        $issues = Cache::loadSave(array('base'  => DIGEST_APP_ID,
                                        'id'    => 'issue_latest'),
                                  'Digest::loadDigests',
                                  array('issue',
                                        'latest',
                                        true));

      } else if ($this->id == 'archive') {
        $issues = Cache::loadSave(array('base'  => DIGEST_APP_ID,
                                        'id'    => 'archive_latest'),
                                  'Digest::loadDigests',
                                  array('archive',
                                        'latest',
                                        true));
      }
    }


    // set issue date
    if (($issue == 'one-year-ago') || ($issue == 'six-months-ago')) {
      // set options
      $this->showUrl = true;

      if ($issue == 'one-year-ago') {
        $timewarp    = '1 year';
        $this->title = _('One Year Ago');
      } else {
        $timewarp    = '6 months';
        $this->title = _('Six Months Ago');
      }

      // calculate date
      $this->issue = Digest::getLastIssueDate($timewarp, true, true);

    } else {
      if (strpos($issue, '/') !== false) {
        $issue       = explode('/', trim($issue, '/'));
        $this->issue = $issue[1];
      } else {
        $this->issue = $issue;
      }

      // pick issue date from list
      if (($this->issue == 'latest') || ($this->issue == 'random')) {
        $this->showUrl = true;

        if ($this->issue == 'latest') {
          // determine latest issue date
          $issue = reset($issues);

        } else if ($this->issue == 'random') {
          // pick a random issue
          $issue = array_rand($issues);
          $issue = $issues[$issue];
        }

        $this->issue = $issue['date'];
      }

      // set title
      $this->title = date('jS F Y', strtotime($this->issue));
    }


    // determine dates of previous / next issues
    $key = Digest::findIssueDate($this->issue, $issues);

    if (($key !== false) || $this->review) {
      // select dates
      if (isset($issues[$key + 1])) {
        $this->prevIssue    = $issues[$key + 1]['date'];
        $this->prevIssueUrl = BASE_URL . '/' . $this->id . '/' . $this->prevIssue . '/';

        if (!$issues[$key + 1]['published']) {
          $this->prevIssueUrl .= '?review';
        }
      }

      if (isset($issues[$key - 1])) {
        $this->nextIssue    = $issues[$key - 1]['date'];
        $this->nextIssueUrl = BASE_URL . '/' . $this->id . '/' . $this->nextIssue . '/';

        if (!$issues[$key - 1]['published']) {
          $this->nextIssueUrl .= '?review';
        }
      }


      // load issue data
      $this->data = Digest::loadDigest($this->issue);

      // review / published sanity check
      if ($this->review && $this->data['published']) {
        // issue has been published. redirect
        Ui::redirect('/issues/' . $this->issue . '/');
      }

      // get areas and types
      $this->areas = Enzyme::getAreas();
      $this->types = Enzyme::getTypes();
    }
  }


  public function draw() {
    // if no data, show message
    if (!$this->data) {
      $buf = _('Issue cannot be found');

      return $buf;
    }

    // draw digest issue title box
    $buf = $this->drawTitleBox();

    // draw intro
    $buf .= $this->container('intro', $this->drawIntro());

    // draw statistics
    $buf .= $this->container('stats', $this->drawStatistics());

    // draw contents
    $buf .= $this->container('contents', $this->drawContents());

    // draw commits
    $buf .= $this->container('commits', $this->drawCommits());

    // draw share / donate box
    $theUrl         = BASE_URL . '/issues/' . $this->data['date'] . '/';
    $theTitle       = 'KDE Commit Digest - Issue ' . $this->data['id'];
    $theDescription = App::truncate(addslashes(str_replace("\n", ' ', strip_tags($this->data['synopsis']))), 995, true);

    $buf .= DigestUi::drawShareBox($theUrl, $theTitle, $theDescription);

    // draw message
    $buf .= $this->drawMessage();

    return $buf;
  }


  public function getScript() {
    return array(
      '//cdnjs.cloudflare.com/ajax/libs/flot/0.8.1/jquery.flot.min.js',
      '//cdnjs.cloudflare.com/ajax/libs/flot/0.8.1/jquery.flot.pie.min.js',
      '/js/jvectormap/jquery.jvectormap.min.js',
      '/js/jvectormap/jquery-jvectormap-world-mill-en.min.js',
      '/js/frame/issueui' . MINIFIED . '.js'
    );
  }


  public function getStyle() {
    return array(
      '/css/frame/issueui' . MINIFIED . '.css'
    );
  }


  public function container($id, $content) {
    $buf = '<div id="' . $id . '" class="container">' .
              $content .
           '</div>';

    return $buf;
  }


  public function drawIntro() {
    // initialise tokens
    $tokens = array(
      'synopsis' => $this->data['synopsis'],
    );

    // prepare text in sections
    if (isset($this->data['sections'])) {
      foreach ($this->data['sections'] as &$section) {
        $section['intro'] = $this->formatIntroTitle($section['intro']);

        if ($section['type'] == 'message') {
          $section['body'] = $this->formatIntroText($section['body']);
        }
      }

      $tokens['sections'] = $this->data['sections'];
    }

    // draw default header elements
    return Renderable::render_static($tokens, 'blocks/issue/intro');
  }


  public function drawStatistics($drawVisuals=true) {
    if (empty($this->data['stats']) || empty($this->data['stats']['general'])) {
      return false;
    }

    // set stats string depending on version
    if ($this->data['version'] == 1) {
      $statsString  = sprintf(_('%d by %d developers, %d lines modified, %d new files'),
                              $this->data['stats']['general']['total_commits'],
                              $this->data['stats']['general']['active_developers'],
                              $this->data['stats']['general']['total_lines'],
                              $this->data['stats']['general']['new_files']);
    } else {
      $statsString  = sprintf(_('%d by %d developers'),
                              $this->data['stats']['general']['total_commits'],
                              $this->data['stats']['general']['active_developers']);
    }

    $buf =   '<h2>' . _('Statistics') . '</h2>

              <table id="stats-general" class="pad">
                <tbody>
                  <tr>
                    <td class="label">' . _('Commits') . '</td>
                    <td>' . $statsString . '</td>
                  </tr>
                  <tr>
                    <td class="label">' . _('Open Bugs') . '</td>
                    <td>' . $this->data['stats']['general']['open_bugs'] . '</td>
                  </tr>
                  <tr>
                    <td class="label">' . _('Open Wishes') . '</td>
                    <td>' . $this->data['stats']['general']['open_wishes'] . '</td>
                  </tr>
                  <tr>
                    <td class="label">' . _('Bugs Opened') . '</td>
                    <td>' . sprintf(_('%d in the last 7 days'), $this->data['stats']['general']['bugs_opened']) . '</td>
                  </tr>
                  <tr>
                    <td class="label">' . _('Bugs Closed') . '</td>
                    <td>' . sprintf(_('%d in the last 7 days'), $this->data['stats']['general']['bugs_closed']) . '</td>
                  </tr>
                </tbody>
              </table>';


    // commit summary
    $buf .= '<h2>' . _('Commit Summary') . '</h2>
             <div id="container-summary">' .
               $this->chartSummary($drawVisuals) .
             '</div>';


    // i18n
    $buf .= '<h2>' . _('Internationalization (i18n) Status') . '</h2>' .
            $this->chartI18n($drawVisuals);


    // bug killers and buzz
    if (!isset($this->data['stats']['buzz'])) {
      $string = _('Bug Killers');
    } else {
      $string = _('Bug Killers and Buzz');
    }

    $buf .= '<h2>' . $string . '</h2>' .
            $this->chartBugsBuzz($drawVisuals);


    // commit countries?
    if ($drawVisuals && isset($this->data['stats']['extended'])) {
      $buf .= '<h2>' . _('Commit Countries') . '</h2>' .
              $this->commitCountries();
    }


    // commit demographics?
    if ($drawVisuals && isset($this->data['stats']['extended'])) {
      $buf .= '<h2>' . _('Commit Demographics') . '</h2>
               <div id="container-demographics">' .
                 $this->chartDemographics() .
              '</div>';
    }

    return $buf;
  }


  private function chartSummary($drawVisuals=true) {
    if (!isset($this->data['stats']['developer'])) {
      return null;
    }

    // set general options
    $options['barwidth'] = 140;

    // module developer
    $options['header']  = array(_('Module'),
                                _('Commits'));
    $statsModule        = new Chart('stats-module', $this->data['stats']['module'], null, null, $options);



    // set header
    if ($this->data['version'] == 1) {
      $options['header']  = array(_('Lines'),
                                  _('Developer'),
                                  _('Commits'));
    } else {
      $options['header']  = array(_('Files'),
                                  _('Developer'),
                                  _('Commits'));
    }

    // process data into correct format for charting
    foreach ($this->data['stats']['developer'] as $key => $value) {
      // set data into array for twin bar chart
      if ($this->data['version'] == 1) {
        $values = array($value['num_lines'],
                        $value['num_commits']);
      } else {
        $values = array($value['num_files'],
                        $value['num_commits']);
      }

      // attempt to set actual developer name
      if (isset($this->data['stats']['people'][$value['identifier']])) {
        $data[$this->data['stats']['people'][$value['identifier']]['name']] = $values;
      } else {
        $data[$value['identifier']] = $values;
      }
    }

    // draw
    $statsDeveloper = new Chart('stats-developer', $data, null, null, $options);

    return $statsModule->drawBar($drawVisuals) .
           $statsDeveloper->drawTwinBar($drawVisuals);
  }


  private function chartI18n($drawVisuals=true) {
    if (!isset($this->data['stats']['i18n'])) {
      return null;
    }

    // set options
    $options['header']    = array(_('Language'),
                                  _('Percentage Complete'));
    $options['barwidth']  = 360;
    $options['percent']   = true;

    // process data into correct format for charting
    foreach ($this->data['stats']['i18n'] as $value) {
      $data[$value['language'] . ' (' . $value['code'] . ')'] = $value['value'];
    }

    // draw
    $statsI18n = new Chart('stats-i18n', $data, null, null, $options);

    return $statsI18n->drawBar($drawVisuals);
  }


  private function chartBugsBuzz($drawVisuals=true) {
    $buf = null;

    // bugfixers
    if (isset($this->data['stats']['bugfixers'])) {
      // set options
      $options['barwidth']  = 360;
      $options['header']    = array(_('Person'),
                                    _('Bugs Closed'));

      // attempt to set actual developer name
      foreach ($this->data['stats']['bugfixers'] as $key => $value) {
        if (isset($this->data['stats']['people'][$key])) {
          $data[$this->data['stats']['people'][$key]['name']] = $value;
        } else {
          $data[$key] = $value;
        }
      }

      // draw
      $statsBugs = new Chart('stats-bugkillers', $data, null, null, $options);
      $buf       = $statsBugs->drawBar($drawVisuals);
    }


    // buzz
    if (isset($this->data['stats']['buzz'])) {
      $limit = 10;

      unset($data);

      if (isset($options)) {
        unset($options['barwidth']);
      }

      // process data into correct format for charting
      $counter = 0;
      foreach ($this->data['stats']['buzz']['program'] as $value) {
        $data[$value['identifier']] = $value['value'];

        if (++$counter == $limit) {
          break;
        }
      }

      $options['header']  = array(_('Program'),
                                  _('Buzz'));
      $statsBuzzProgram   = new Chart('stats-buzz-program', $data, null, null, $options);


      // process data into correct format for charting
      unset($data);

      $counter = 0;
      foreach ($this->data['stats']['buzz']['person'] as $value) {
        // attempt to set actual developer name
        if (isset($this->data['stats']['people'][$value['identifier']])) {
          $data[$this->data['stats']['people'][$value['identifier']]['name']] = $value['value'];
        } else {
          $data[$value['identifier']] = $value['value'];
        }

        if (++$counter == $limit) {
          break;
        }
      }

      $options['header']  = array(_('Person'),
                                  _('Buzz'));
      $statsBuzzPerson    = new Chart('stats-buzz-person', $data, null, null, $options);


      // draw
      $buf .=  '<div id="container-buzz">' .
                  $statsBuzzProgram->drawBar($drawVisuals) .
                  $statsBuzzPerson->drawBar($drawVisuals) .
               '</div>';
    }

    return $buf;
  }


  private function commitCountries() {
    // prepare country data
    foreach ($this->data['stats']['extended']['country'] as $country => $percent) {
      if ($country == 'unknown') {
        continue;
      }

      $countryData[strtoupper($country)] = $percent;
    }

    // output HTML and JS setup code
    $buf = '<div id="worldmap"></div>

            <script>
                window.countryData = ' . json_encode($countryData) . ';
            </script>';

    return $buf;
  }


  private function chartDemographics() {
    // sex
    $data             = $this->data['stats']['extended']['gender'];
    $statsSex         = new Chart('stats-sex', $data, _('Sex'));

    // TODO: disable motivation charting until generation is fixed in Enzyme
//    // motivation
//    $data             = $this->data['stats']['extended']['motivation'];
//    $statsMotivation  = new Chart('stats-motivation', $data, _('Motivation'));

    // age
    $data             = $this->data['stats']['extended']['age'];
    $statsAge         = new Chart('stats-age', $data, _('Age'));


    // draw
    return $statsSex->drawPie() .
           $statsAge->drawPie();

    // TODO: disable motivation charting until generation is fixed in Enzyme
//           $statsMotivation->drawPie();
  }


  private function drawContents() {
    if (empty($this->data['commits'])) {
      return false;
    }

    // iterate looking for commits within each type / area
    foreach ($this->data['commits'] as $commit) {
      $contents[$commit['type']][$commit['area']] = true;
    }

    // draw
    $buf =   '<h2>' . _('Contents') . '</h2>

              <table id="contents-table">
                <thead>
                  <tr>
                    <th>&nbsp;</th>';

    // draw column header
    foreach ($this->types as $type) {
      $buf .=  '  <th>' . $type . '</th>';
    }

    $buf .=  '    </tr>
                </thead>

                <tbody>';

    // draw rows
    $counterArea = 1;

    foreach ($this->areas as $areaId => $area) {
      $buf .=  '<tr>
                  <td>' . $area . '</td>';

      // check each type, show icon if we have commits
      $counterType = 1;

      foreach ($this->types as $typeId => $type) {
        if (isset($contents[$counterType++][$counterArea])) {
          // commits found for this section
          $buf .=  '<td>
                      <a class="n icon-' . $typeId . '" href="#' . $typeId . '-' . $areaId . '" title="' . sprintf(_('Jump to %s / %s'), $type, $area) . '">&nbsp;</a>
                    </td>';

        } else {
          // no commits in this section
          $buf .=  '<td>&nbsp;</td>';
        }
      }

      $buf .=  '</tr>';

      ++$counterArea;
    }

    // close table
    $buf .=  '  </tbody>
              </table>';

    // show number of selections
    if (isset($this->data['commits'])) {
      $buf .=  '<p id="num-selections">' .
                  sprintf(_('There are %d selections this week'), count($this->data['commits'])) .
               '</p>';
    }

    return $buf;
  }


  private function drawCommits() {
    $lastType = null;
    $lastArea = null;

    $buf      = null;

    if (!empty($this->data['commits'])) {
      foreach ($this->data['commits'] as $commit) {
        // draw new header (type)?
        if ($commit['type'] != $lastType) {
          $type = array_slice($this->types, ($commit['type'] - 1), 1);
          $buf .= '<h2>' . reset($type) . '</h2>';

          $lastType = $commit['type'];
        }

        // draw new subheader (area)?
        if ($commit['area'] != $lastArea) {
          $area = array_slice($this->areas, ($commit['area'] - 1), 1);

          $buf .= '<a id="' . key($type) . '-' . key($area) . '"></a>
                   <h2>' . reset($area) . '</h2>';

          $lastArea = $commit['area'];
        }

        // draw commit
        $buf .= Digest::drawCommit($commit, $this->issue);
      }

    } else {
      // no commits found
      $buf .= '<p class="prompt">' .
                 _('No commits found') .
              '</p>';
    }

    return $buf;
  }


  private function drawMessage() {
    $buf = '<p class="message">' . sprintf(_('Thanks for reading the %s!'), Config::getSetting('enzyme', 'PROJECT_NAME')) . '</p>';

    return $buf;
  }


  private function formatIntroTitle($text) {
    return Digest::replacePeopleReferences($this->data, $text);
  }


  private function formatIntroText($text) {
    // change links
    $text = str_replace('/issues/', BASE_URL . '/issues/', $text);

    // replace people references
    $text = Digest::replacePeopleReferences($this->data, $text);

    // replace media references
    $text = $this->replaceMediaReferences($text);

    return $text;
  }


  private function replaceMediaReferences($text) {
    // images
    if (isset($this->data['image'])) {
      foreach ($this->data['image'] as $media) {
        $text = str_replace('[image' . $media['number'] . ']', Media::draw($media), $text);
      }
    }

    // videos
    if (isset($this->data['video'])) {
      foreach ($this->data['video'] as $media) {
        $text = str_replace('[video' . $media['number'] . ']', Media::draw($media), $text);
      }
    }

    return $text;
  }


  public function drawTitleBox($context = 'issues') {
    $prev        = null;
    $next        = null;
    $issueTitle  = null;

    // previous issue button?
    if ($this->prevIssue) {
      $prev = '<a class="left n" href="' . $this->prevIssueUrl . '" title="' . sprintf(_('Go to the previous digest issue (%s)'), $this->prevIssue) . '">&nbsp;</a>';
    }

    // next issue button?
    if ($this->nextIssue) {
      $next = '<a class="right n" href="' . $this->nextIssueUrl . '" title="' . sprintf(_('Go to the next digest issue (%s)'), $this->nextIssue) . '">&nbsp;</a>';
    }

    // show issue number?
    if ($this->id == 'issues') {
      $issueTitle =  '<h1>' .
                        sprintf(_('Issue %d'), $this->data['id']) .
                     '</h1>';
    }

    // determine author
    $authorDetails = Digest::getAuthorDetails($this->data['author']);
    $author        = '<a href="mailto:' . $authorDetails['email'] . '">' .
                        sprintf(_('by %s'), Digest::formatName($authorDetails)) .
                     '</a>';


    // draw box
    $buf = null;

    if ($this->showUrl) {
      $buf .= '<div id="issue-url">' .
                 sprintf(_('Located at %s'), '<a href="' . BASE_URL . '/' . $this->id . '/' . $this->data['date'] . '/">' . BASE_URL . '/' . $this->id . '/' . $this->data['date'] . '/</a>') .
              '</div>';
    }

    $buf .=  '<div id="title-box" class="filled title-box-' . $this->data['type'] . '">' .
                $prev .
             '  <div class="mid">' .
                  $issueTitle .
             '    <h3>' . Date::get('full', $this->issue) . '</h3>' . ' ' .
                  $author .
             '  </div>' .
                $next .
             '</div>';


    // show contributors?
    $users = Digest::getUsersByPermission();

    if (isset($this->data['version']) && ($this->data['version'] > 1) && !empty($this->data['contributors'])) {
      // set name of contributors, only show each once!
      foreach ($this->data['contributors'] as $contributor) {
        $contributors[$contributor['name']] = $users[$contributor['name']];
      }

      array_unique($contributors);

      // draw
      $buf .=  '<div id="contributors-box">
                  <h3>' . _('Contributors') . '</h3>
                  <div id="contributors-box-inner">' .
                    implode('<br />', $contributors) .
               '  </div>
                </div>';
    }

    return $buf;
  }
}

?>