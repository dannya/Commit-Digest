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


class DigestUi extends Renderable {
  public $frame             = null;

  private $style            = array();
  private $appScript        = array();

  private $userScript       = null;


  public function __construct() {
    // set style and script references
    $this->style[] = '/css/includes/common' . MINIFIED . '.css';
    $this->appScript[] = '//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery' . MINIFIED . '.js';

    parent::__construct();

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

              <meta name="google-site-verification" content="KW812FIwZ9rCr4OvsxJA2EcG6y6RaOKu3-EF89uqA4s" />

              <meta name="viewport" content="width=device-width, initial-scale=1.0" />

              <link rel="shortcut icon" href="' . BASE_URL .'/favicon.ico" type="image/x-icon" />
              <link rel="icon" href="' . BASE_URL . '/favicon.ico" type="image/x-icon" />

              <link rel="alternate" type="application/rss+xml" title="" href="' . BASE_URL . '/updates/" />

              <script>
                  window.vars = {
                      "ENZYME_URL": "' . Config::getSetting('enzyme', 'ENZYME_URL') . '"
                  };
              </script>';

    return $buf;
  }


  public function drawStyle() {
    // compile
    $theStyle = $this->style;

    // draw
    $buf = null;

    foreach ($theStyle as $style) {
      $buf .= '<link rel="stylesheet" href="' . BASE_URL . $style . '" type="text/css" media="screen" />' . "\n";
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

      $buf .= '<script src="' . $url . '"></script>' . "\n";
    }

    return $buf;
  }


  public function getBodyClasses() {
    $class = '';

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

    $tokens = array();

    // show review warning banner?
    if (($this->frame instanceof IssueUi) && $this->frame->review) {
      $tokens = array(
        'is_review'     => true,
        'issue_date'    => $this->frame->issue,
      );
    }

    // draw language selector
    $tokens['htmlselector'] = Ui::htmlSelector(
                                'language',
                                Digest::getLanguages(),
                                LANGUAGE,
                                'changeLanguage(event);'
                              );

    // draw default header elements
    return parent::render($tokens, 'blocks/header');
  }


  public function drawSidebar() {
    if (isset($this->frame->noFrame) && $this->frame->noFrame) {
      return null;
    }

    $tokens = array();

    return parent::render($tokens, 'blocks/sidebar');
  }


  public function drawContent() {
    $buf = $this->frame->draw();

    if (isset($this->frame->noFrame) && $this->frame->noFrame) {
      return $buf;

    } else {
      // wrap in frame div
      return '<div id="frame">' .
                $buf .
             '</div>';
    }
  }


  public function drawFooter() {
    if (isset($this->frame->noFrame) && $this->frame->noFrame) {
      return null;
    }

    $buf = '<div id="footer>' .
              sprintf(
                _('%s by <a href="%s">%s</a> and the <a href="%s">%s team</a>, %s.'),
                Config::getSetting('enzyme', 'PROJECT_NAME'),
                'http://dannya.com/',
                Config::$meta['author'],
                'mailto:digest@kde.org',
                Config::$app['name'],
                '2006-2015'
              ) .
              '<br />' .
              _('All issues in <a href="/archive/">archive</a> by Derek Kite.') .
           '  <a id="enzyme-credit" href="http://enzyme-project.org/" target="_blank" title="' . _('Powered by Enzyme') . '">' . _('Powered by Enzyme') . '</a>
            </div>';

    return $buf;
  }


  public static function drawShareBox($theUrl, $theTitle, $theDescription) {
    // set variables
    $theUrlEncode   = urlencode($theUrl);
    $theTitleEncode = urlencode($theTitle);

    $button['rss']      = BASE_URL . '/updates/';
    $button['email']    = 'http://www.addtoany.com/add_to/email?linkurl=' . $theUrlEncode . '&amp;linkname=' . $theTitleEncode;
    $button['identica'] = 'http://www.addtoany.com/add_to/identi_ca?linkurl=' . $theUrlEncode . '&amp;type=page&amp;linkname=' . $theTitleEncode;
    $button['twitter']  = 'http://www.addtoany.com/add_to/twitter?linkurl=' . $theUrlEncode . '&amp;type=page&amp;linkname=' . $theTitleEncode;
    $button['facebook'] = 'http://www.addtoany.com/add_to/facebook?linkurl=' . $theUrlEncode . '&amp;type=page&amp;linkname=' . $theTitleEncode;

    // draw
    $buf = '<div id="share-box" class="share-sidebar">
              <div id="donate">
                <div id="flattr">&nbsp;</div>
              </div>

              <script src="http://api.flattr.com/js/0.5.0/load.js"></script>
              <script>
                var theUrl         = "' . $theUrl . '";
                var theTitle       = "' . $theTitle . '";
                var theDescription = "' . $theDescription . '";
              </script>

              <div id="share-buttons">
                <a id="button-rss" class="button" target="_blank" href="' . $button['rss'] . '" title="' . sprintf(_('Subscribe to %s updates'), Config::getSetting('enzyme', 'PROJECT_NAME')) . '">&nbsp;</a>
                <a id="button-email" class="button" target="_blank" href="' . $button['email'] . '" title="' . _('Send this issue by email...') . '">&nbsp;</a>
                <a id="button-twitter" class="button" target="_blank" href="' . $button['twitter'] . '" title="' . _('Share this issue on Twitter...') . '">&nbsp;</a>
                <a id="button-facebook" class="button" target="_blank" href="' . $button['facebook'] . '" title="' . _('Share this issue on Facebook...') . '">&nbsp;</a>
                <a id="button-identica" class="button" target="_blank" href="' . $button['identica'] . '" title="' . _('Share this issue on Identica...') . '">&nbsp;</a>
              </div>
            </div>';

    return $buf;
  }
}

?>