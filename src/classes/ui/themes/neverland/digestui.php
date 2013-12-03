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


class DigestUi {
  public $frame             = null;

  private $style            = array('//cdn.kde.org/css/bootstrap.css',
                                    '//cdn.kde.org/css/bootstrap-responsive.css',
                                    '/classes/ui/themes/neverland/css/neverland.css');
  private $appScript        = array('//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js');

  private $userScript       = null;


  public function __construct() {
    // determine current frame
    if (isset($_GET['page'])) {
      $current = trim($_GET['page'], '/');
    } else {
      $current = null;
    }

    if (isset($_GET['issue'])) {
      $issue = trim(str_replace($current . '/', null, $_GET['issue']), '/');
    } else {
      $issue = null;
    }


    // initialise UI
    if (($current == 'issues') || ($current == 'archive')) {
      if (empty($issue)) {
        $this->frame = new ListUI($current);
      } else {
        $this->frame = new IssueUI($current, $_GET['issue']);
      }

    } else if (($current == 'six-months-ago') || ($current == 'one-year-ago') || ($current == 'random')) {
      $this->frame = new IssueUI('issues', $current);

    } else if ($current == 'moreinfo') {
      $this->frame = new MoreInfoUi();

    } else if ($current == 'statistics') {
      $this->frame = new StatisticsUi($current, $issue);

    } else if ($current == 'contribute') {
      $this->frame = new ContributeUi();

    } else if ($current == 'options') {
      $this->frame = new OptionsUi();

    } else if ($current == 'data') {
      $this->frame = new DataUi();

    } else if ($current == 'terms') {
      $this->frame = new DataTermsUi();

    } else if ($current == 'developer') {
      $this->frame = new DeveloperProfileUi();

    } else {
      $this->frame = new IndexUi();
    }

    // get specific style
    $this->style = array_merge($this->style, $this->frame->getStyle());

    // set script
    $this->userScript[] = '/js/index.php?script=common&amp;id=' . $this->frame->id;
  }


  public function drawTitle() {
    $buf = '<title>' . Config::$app['name'] . ' - ' . $this->frame->title . '</title>';

    return $buf;
  }


  public function drawMeta() {
    if (isset(Config::$meta['keywords']) && Config::$meta['keywords']) {
      $buf = '<meta name="author" content="' . Config::$meta['author'] . '" />';
    } else {
      $buf = null;
    }

    $buf .=  '<meta charset="utf-8" />

              <meta name="description" content="' . Config::$meta['description'] . '" />
              <meta name="keywords" content="' . Config::$meta['keywords'] . '" />

              <meta name="viewport" content="width=device-width, initial-scale=1.0" />

              <link rel="shortcut icon" href="' . BASE_URL .'/favicon.ico" type="image/x-icon" />
              <link rel="icon" href="' . BASE_URL . '/favicon.ico" type="image/x-icon" />

              <link rel="alternate" type="application/rss+xml" title="" href="' . BASE_URL . '/updates/" />';

    return $buf;
  }


  public function drawStyle() {
    // compile
    $theStyle = $this->style;
    // draw
    $buf = null;

    foreach ($theStyle as $style) {
      $buf .= '<link rel="stylesheet" href="' . $style . '" type="text/css" media="screen" />' . "\n";
    }

    return $buf;
  }


  public function drawScript() {
    // merge script file lists
    $theScript = array_merge(
      $this->appScript,
      $this->userScript,
      $this->frame->getScript()
    );

    // draw out script
    $buf = null;

    foreach ($theScript as $script) {
      if (strpos($script, '//') === 0) {
        $url = $script;
      } else {
        $url = BASE_URL . $script;
      }

      $buf .= '<script type="text/javascript" src="' . $url . '"></script>' . "\n";
    }

    return $buf;
  }


  public function getBodyClasses() {
    $class = null;

    // add theme name
    if (isset(Config::$theme) && (Config::$theme[0] !== 'default')) {
      $class .= Config::$theme[0];
    } else {
      $class .= 'default';
    }

    if (($this->frame instanceof IssueUi) && $this->frame->review) {
      // showing review warning banner
      $class .= ' review';
    }

    return $class;
  }


  public function drawHeader() {
    if (isset($this->frame->noFrame) && $this->frame->noFrame) {
      return null;
    }

    $buf = null;

    // show review warning banner
    if (($this->frame instanceof IssueUi) && $this->frame->review) {
      $buf  .= '<div id="header-review">' .
                  _('This issue has not been published yet') .
               '  <input type="button" value="' . _('Publish') . '" onclick="setPublished(\'' . $this->frame->issue . '\', true);" />
                </div>

                <iframe id="header-review-target" src="http://www.something.com/" style="display:none;"></iframe>

                <script type="text/javascript">
                  function setPublished(date, state) {
                    if ((typeof date == "undefined") || (typeof state == "undefined")) {
                      return false;
                    }

                    // send request through iframe
                    $("header-review-target").src = "' . Config::getSetting('enzyme', 'ENZYME_URL') . '/get/publish.php?date=" + date + "&state=" + state;

                    // remove header
                    if ($("header-review")) {
                      Element.remove($("header-review"));
                      $("body").removeClassName("review");
                      $("sidebar").style.top = (parseInt($("sidebar").style.top) - 34) + "px";
                    }
                  }
                </script>';
    }


    // draw default header elements
    $buf  .= '<div id="top-small" class="navbar navbar-fixed-top Neverland">
                <div id="header-bar" class="navbar-inner">
                  <div class="container">
                    <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                      <span class="icon-bar"></span>
                      <span class="icon-bar"></span>
                      <span class="icon-bar"></span>
                    </a>
                    <a class="brand" href="'. BASE_URL .'">
                      <img src="//cdn.kde.org/img/logo.plain.small.png"/>
                      ' . Config::$app['name'] . '
                    </a>
                    <div class="nav-pills pull-right">
                      <div id="language-selector">' .
                        Ui::htmlSelector('language', Digest::getLanguages(), LANGUAGE, 'changeLanguage(event);') . '
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="container Neverland home">
                <div id="pageRow" class="row">';

    return $buf;
  }


  public function drawSidebar() {
    if (isset($this->frame->noFrame) && $this->frame->noFrame) {
      return null;
    }

    // draw
    $buf = '<div id="sidebar" class="span2 pull-right">

              <ul id="navSidebarNeverland" class="nav nav-list well Neverland">
                <li class="spacer">
                  <a href="' . BASE_URL . '/" title="' . _('Front Page') . '">' . _('Front Page') . '</a>
                </li>

                <li class="spacer">
                  <a href="' . BASE_URL . '/issues/" title="' . _('Issues') . '">' . _('Issues') . '</a>
                  <ul>
                    <li>
                      <a href="' . BASE_URL . '/issues/latest/" title="' . _('Latest Issue') . '">' . _('Latest Issue') . '</a>
                    </li>
                  </ul>
                </li>

                <li class="spacer">
                  <a href="' . BASE_URL . '/archive/" title="' . _('Archive') . '">' . _('Archive') . '</a>
                </li>

                <li>
                  <a href="' . BASE_URL . '/six-months-ago/" title="' . _('Six Months Ago') . '">' . _('Six Months Ago') . '</a>
                </li>
                <li>
                  <a href="' . BASE_URL . '/one-year-ago/" title="' . _('One Year Ago') . '">' . _('One Year Ago') . '</a>
                </li>
                <li class="spacer">
                  <a href="' . BASE_URL . '/issues/random/" title="' . _('Random Digest') . '">' . _('Random Digest') . '</a>
                </li>

                <li class="spacer">
                  <a href="' . BASE_URL . '/contribute/" title="' . _('Contribute') . '">' . _('Contribute') . '</a>
                </li>

                <li class="spacer">
                  <a href="' . BASE_URL . '/data/" title="' . _('Data') . '">' . _('Data') . '</a>
                </li>
              </ul>

              <div id="sidebar-bottom">
                &nbsp;
              </div>
            </div>';

    return $buf;
  }


  public function drawContent() {
    $buf = $this->frame->draw();

    if (isset($this->frame->noFrame) && $this->frame->noFrame) {
      return $buf;

    } else {
      // wrap in frame div
      return '<div id="frame_page" class="span10">' .
                $buf .
             '</div>';
    }
  }


  public function drawFooter() {
    if (isset($this->frame->noFrame) && $this->frame->noFrame) {
      return null;
    }

    $buf = '</div>
            <footer class="Neverland">' .
              sprintf(_('%s by <a href="mailto:%s">%s</a>, %s'), Config::getSetting('enzyme', 'PROJECT_NAME'), 'danny@commit-digest.org', 'Danny Allen', '2006-2013') .
              '<br />' .
              _('All issues in <a href="/archive/">archive</a> by Derek Kite') .
           '  <a id="enzyme-credit" href="http://enzyme-project.org/" target="_blank" title="' . _('Powered by Enzyme') . '">' . _('Powered by Enzyme') . '</a>
            </footer>
          </div>';

    return $buf;
  }


  public static function drawShareBox($theUrl, $theTitle, $theDescription) {
    // draw
    $buf = '<div id="share-box" class="share-sidebar">
              <script type="text/javascript" src="http://api.flattr.com/js/0.5.0/load.js"></script>
              <script type="text/javascript">
                var theUrl         = "' . $theUrl . '";
                var theTitle       = "' . $theTitle . '";
                var theDescription = "' . $theDescription . '";
              </script>

              <div id="donate">
                <div id="flattr">&nbsp;</div>
              </div>
            </div>';

    return $buf;
  }
}

?>