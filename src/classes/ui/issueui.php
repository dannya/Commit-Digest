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
    $theUrl         = BASE_URL . '/issue/' . $this->data['date'] . '/';
    $theTitle       = 'KDE Commit Digest - Issue ' . $this->data['id'];
    $theDescription = App::truncate(addslashes(str_replace("\n", ' ', strip_tags($this->data['synopsis']))), 995, true);

    $buf .= DigestUi::drawShareBox($theUrl, $theTitle, $theDescription);

    // draw message
    $buf .= $this->drawMessage();

    return $buf;
  }


  public function getScript() {
    return array('/js/plotr.js',
                 '/js/frame/issueui.js');
  }


  public function getStyle() {
    return array('/css/issueui.css');
  }


  public function container($id, $content) {
    $buf = '<div id="' . $id . '" class="container">' .
              $content .
           '</div>';

    return $buf;
  }


  private function drawIntro() {
    $buf = '<h1>' . _('This Week...') . '</h1>';

    // synopsis
    $buf .= '<div class="synopsis">' .
               $this->data['synopsis'] .
            '</div>';

    // messages
    if (isset($this->data['sections'])) {
      $counter = 1;

      foreach ($this->data['sections'] as $section) {
        $buf .=  '<div class="body-row">
                    <div class="body-title">
                      <a id="intro-' . $counter . '" name="intro-' . $counter . '"></a>' .
                      $this->formatIntroTitle($section['intro']) .
                 '  </div>';

        if ($section['type'] == 'message') {
          $buf .= '<div class="body-text filled">' .
                     $this->formatIntroText($section['body']) .
                  '</div>';
        }

        $buf .= '</div>';

        ++$counter;
      }
    }

    return $buf;
  }


  public function drawStatistics() {
    if (empty($this->data['stats'])) {
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

    $buf =   '<h1>' . _('Statistics') . '</h1>

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
               $this->chartSummary() .
             '</div>';


    // i18n
    $buf .= '<h2>' . _('Internationalization (i18n) Status') . '</h2>' .
            $this->chartI18n();


    // bug killers and buzz
    if (!isset($this->data['stats']['buzz'])) {
      $string = _('Bug Killers');
    } else {
      $string = _('Bug Killers and Buzz');
    }

    $buf .= '<h2>' . $string . '</h2>' .
            $this->chartBugsBuzz();


    // commit countries
    if (isset($this->data['stats']['extended'])) {
      $buf .= '<h2>' . _('Commit Countries') . '</h2>' .
              $this->commitCountries();
    }


    // commit demographics
    if (isset($this->data['stats']['extended'])) {
      $buf .= '<h2>' . _('Commit Demographics') . '</h2>
               <div id="container-demographics">' .
                 $this->chartDemographics() .
              '</div>';
    }

    return $buf;
  }


  private function chartSummary() {
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

    return $statsModule->drawBar() .
           $statsDeveloper->drawTwinBar();
  }


  private function chartI18n() {
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

    return $statsI18n->drawBar(true);
  }


  private function chartBugsBuzz() {
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
      $buf       = $statsBugs->drawBar();
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
                  $statsBuzzProgram->drawBar() .
                  $statsBuzzPerson->drawBar() .
               '</div>';
    }

    return $buf;
  }


  private function commitCountries() {
    // get country names
    $countries = Digest::getCountries();


    // set location of images
    if (false && ($this->data['date'] == '2010-10-10')) {
      $imgDir = '/files/stats2';
    } else {
      $imgDir = '/files/stats';
    }


    // format countries into two columns (restrict to 14 per column)
    $fullCounter  = 0;
    $counter      = 0;
    $column       = 0;
    $numFields    = count($this->data['stats']['extended']['country']);
    $limitFields  = min($numFields, 28);

    foreach ($this->data['stats']['extended']['country'] as $country => $percent) {
      // stop when we reach limit
      if ($fullCounter++ == $limitFields) {
        break;
      }

      // go to next column?
      if ($counter == ceil($limitFields / 2)) {
        $counter = 0;
        $column  = 1;
      }

      // put into array
      $tmp = array($country => array('country'  => $country,
                                     'percent'  => $percent));

      // add to array
      $theFields[$counter++][$column] = $tmp;
    }


    // don't try and show image if file doesn't exist (we will generate and fetch them later)
    if (file_exists(BASE_DIR . '/issues/' . $this->data['date'] . $imgDir . '/standard-embedded_world.png')) {
      $image = BASE_URL . '/issues/' . $this->data['date'] . $imgDir . '/standard-embedded_world.png';
    } else {
      $image = BASE_URL . '/img/bgd.png';
    }


    $buf = '<map name="regions">
              <area href="?view=2009-02-22&amp;scheme=map_default&amp;region=europe&amp;mode=embedded&amp;standalone=no" alt="Europe" title="Europe" shape="rect" coords="200,0,345,135">
              <area href="?view=2009-02-22&amp;scheme=map_default&amp;region=africa&amp;mode=embedded&amp;standalone=no" alt="Africa" title="Africa" shape="rect" coords="200,135,400,370">
              <area href="?view=2009-02-22&amp;scheme=map_default&amp;region=oceania&amp;mode=embedded&amp;standalone=no" alt="Oceania" title="Oceania" shape="rect" coords="400,200,600,370">
              <area href="?view=2009-02-22&amp;scheme=map_default&amp;region=asia&amp;mode=embedded&amp;standalone=no" alt="Asia" title="Asia" shape="rect" coords="345,0,600,200">
              <area href="?view=2009-02-22&amp;scheme=map_default&amp;region=north-america&amp;mode=embedded&amp;standalone=no" alt="North America" title="North America" shape="rect" coords="0,0,200,175">
              <area href="?view=2009-02-22&amp;scheme=map_default&amp;region=south-america&amp;mode=embedded&amp;standalone=no" alt="South America" title="South America" shape="rect" coords="0,175,200,370">
            </map>

            <div id="mappy-container">
              <div id="mappy-nav-top" class="mappy-horiz rt"><div>&nbsp;</div></div>
              <div id="mappy-nav-left" class="mappy-vert rl"><div>&nbsp;</div></div>

              <div id="mappy-content">
                <span id="mappy-content-prompt">' .
                  _('Click on the map regions to zoom in and zoom out...') .
           '    </span>
                <img id="mappy-content-spinner" src="' . BASE_URL . '/img/spinner.gif" alt="" />

                <div id="mappy-content-overlay">
                  <div id="mappy-content-overlay-na" title="' . _('North America') . '" onclick="changeMap(\'' . $this->data['date'] . '\', \'north-america\');">&nbsp;</div>
                  <div id="mappy-content-overlay-sa" title="' . _('South America') . '" onclick="changeMap(\'' . $this->data['date'] . '\', \'south-america\');">&nbsp;</div>
                  <div id="mappy-content-overlay-eu" title="' . _('Europe') . '" onclick="changeMap(\'' . $this->data['date'] . '\', \'europe\');">&nbsp;</div>
                  <div id="mappy-content-overlay-af" title="' . _('Africa') . '" onclick="changeMap(\'' . $this->data['date'] . '\', \'africa\');">&nbsp;</div>
                  <div id="mappy-content-overlay-as" title="' . _('Asia') . '" onclick="changeMap(\'' . $this->data['date'] . '\', \'asia\');">&nbsp;</div>
                  <div id="mappy-content-overlay-oc" title="' . _('Oceania') . '" onclick="changeMap(\'' . $this->data['date'] . '\', \'oceania\');">&nbsp;</div>
                </div>
                <img id="mappy-content-img" src="' . $image . '" alt="" title="' . _('Click to zoom out...') . '" />

                <script type="text/javascript">
                  // observe image load
                  Event.observe($("mappy-content-img"), "load", changeMapLoaded);
                </script>

                <table id="mappy-content-table" style="display:none;">
                  <tbody>';

    foreach ($theFields as $column => $data) {
      $current = reset($data[0]);

      // set name string
      if (!empty($countries[$current['country']]['name'])) {
        $nameString = $countries[$current['country']]['name'];
      } else {
        $nameString = $current['country'];
      }

      $buf .=  '<tr>
                  <td class="label">' . $current['percent'] . '%</td>
                  <td class="value">
                    <div id="flag-' . $current['country'] . '" class="flag">&nbsp;</div>' .
                    $nameString .
               '  </td>';

      // column2
      if (isset($data[1])) {
        $current = reset($data[1]);

        // set name string
        if (!empty($countries[$current['country']]['name'])) {
          $nameString = $countries[$current['country']]['name'];
        } else {
          $nameString = $current['country'];
        }

        $buf .=  '  <td class="label">' . $current['percent'] . '%</td>
                    <td class="value">
                      <div id="flag-' . $current['country'] . '" class="flag">&nbsp;</div>' .
                      $nameString .
                 '  </td>
                  </tr>';
      }
    }

    $buf .=  '    </tbody>
                </table>
              </div>

              <div id="mappy-nav-right" class="mappy-vert rr"><div>&nbsp;</div></div>
              <div id="mappy-nav-bottom" class="mappy-horiz rb"><div>&nbsp;</div></div>
            </div>

            <div id="mappy-legend">
              <div class="mappy-legend-item">
                <div id="mappy-legend-item-1" class="box">&nbsp;</div> 0%
              </div>
              <div class="mappy-legend-item">
                <div id="mappy-legend-item-2" class="box">&nbsp;</div> 0-1%
              </div>
              <div class="mappy-legend-item">
                <div id="mappy-legend-item-3" class="box">&nbsp;</div> 1-2%
              </div>
              <div class="mappy-legend-item">
                <div id="mappy-legend-item-4" class="box">&nbsp;</div> 2-10%
              </div>
              <div class="mappy-legend-item">
                <div id="mappy-legend-item-5" class="box">&nbsp;</div> +10%
              </div>

              <a id="mappy-change-list" class="n" href="#" onclick="mappy(event, \'' . $this->data['date'] . '\', \'list\');">' . _('View as list...') . '</a>
              <a id="mappy-change-map" class="n" href="#" onclick="mappy(event, \'' . $this->data['date'] . '\', \'map\');" style="display:none;">' . _('View as map...') . '</a>
            </div>';

    // if map images not available, initiate generation job, then wait for results
    if (!file_exists(BASE_DIR . '/issues/' . $this->data['date'] . $imgDir . '/standard-embedded_world.png')) {
      $buf .=  '<script type="text/javascript">
                  var theData = ' . json_encode($this->data['stats']['extended']['country']) . ';

                  getMap(\'' . $this->data['date'] . '\', theData);
                </script>';
    }

    return $buf;
  }


  private function chartDemographics() {
    // sex
    $data             = $this->data['stats']['extended']['gender'];
    $statsSex         = new Chart('stats-sex', $data, _('Sex'));

    // motivation
    $data             = $this->data['stats']['extended']['motivation'];
    $statsMotivation  = new Chart('stats-motivation', $data, _('Motivation'));

    // age
    $data             = $this->data['stats']['extended']['age'];
    $statsAge         = new Chart('stats-age', $data, _('Age'));


    // draw
    return $statsSex->drawPie() .
           $statsAge->drawPie() .
           $statsMotivation->drawPie();
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
    $buf =   '<h1>' . _('Contents') . '</h1>

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
          $buf .= '<h1>' . reset($type) . '</h1>';

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
      $issueTitle =  '<h2>' .
                        sprintf(_('Issue %d'), $this->data['id']) .
                     '</h2>';
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
             '    <h3>' . Date::get('full', $this->issue) . '</h3>' .
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