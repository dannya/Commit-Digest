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

  private $style            = array('/css/includes/common.css');
  private $appScript        = array('/js/jquery.js');

  private $userScript       = null;


  public function __construct() {
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

              <meta name="viewport" content="width=device-width, initial-scale=1.0" />

              <link rel="shortcut icon" href="' . BASE_URL .'/favicon.ico" type="image/x-icon" />
              <link rel="icon" href="' . BASE_URL . '/favicon.ico" type="image/x-icon" />

              <link rel="alternate" type="application/rss+xml" title="" href="' . BASE_URL . '/updates/" />';

    return $buf;
  }


  public function drawStyle() {
    // compile
    $theStyle = $this->style;

    if (LIVE_SITE) {
      $theStyle = array(Cache::getMinCss('web_' . get_class($this->frame), $theStyle));
    }


    // draw
    $buf = null;

    foreach ($theStyle as $style) {
      $buf .= '<link rel="stylesheet" href="' . BASE_URL . $style . '" type="text/css" media="screen" />' . "\n";
    }

    return $buf;
  }


  public function drawScript() {
    if (!LIVE_SITE) {
      // don't use minified and cached version on dev
      $theScript = array_merge($this->appScript,
                               $this->userScript,
                               $this->frame->getScript());
    } else {
      // use cached and minified versions
      $theScript = $this->userScript;
      array_unshift($theScript, Cache::getMinJs('app', $this->appScript));

      $frameScript = $this->frame->getScript();

      if (!empty($frameScript)) {
        $theScript[] = Cache::getMinJs($this->frame->id, $frameScript);
      }
    }

    // draw out script
    $buf = null;

    foreach ($theScript as $script) {
      $buf .= '<script type="text/javascript" src="' . BASE_URL . $script . '"></script>' . "\n";
    }

    return $buf;
  }


  public function getBodyClasses() {
    $class = null;

    if (($this->frame instanceof IssueUi) && $this->frame->review) {
      // showing review warning banner
      $class .= ' class="review"';
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

    $buf = '<div id="footer">' .
              sprintf(_('%s by <a href="mailto:%s">%s</a>, %s'), Config::getSetting('enzyme', 'PROJECT_NAME'), 'danny@commit-digest.org', 'Danny Allen', '2006-2013') .
              '<br />' .
              _('All issues in <a href="/archive/">archive</a> by Derek Kite') .
           '  <a id="enzyme-credit" href="http://enzyme-project.org/" target="_blank">&nbsp;</a>
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


    // define script
    $script =  '$(function () {
                  // check if we have space for elements
                  $(window).on("resize", function () {
                    if ($(window).height() < 560) {
                      $("#share-box").attr("class", "share-bottom");
                    } else {
                      $("#share-box").attr("class", "share-sidebar");
                    }
                  });

                  // hide elements based on initial size?
                  if ($(window).height() < 550) {
                    $("#share-box").attr("class", "share-bottom");

                    // set flattr button size
                    var flattrButton = "compact";

                  } else {
                    $("#share-box").attr("class", "share-sidebar");

                    // set flattr button size
                    if ($(window).height() < 600) {
                      var flattrButton = "compact";
                    } else {
                      var flattrButton = "default";
                    }
                  }

                  // setup flattr button
                  FlattrLoader.setup();

                  FlattrLoader.render({
                    "uid":          "dannya",
                    "button":       flattrButton,
                    "language":     "en_GB",
                    "category":     "text",
                    "url":          theUrl,
                    "title":        theTitle,
                    "description":  theDescription
                  }, "flattr", "replace");
                });';


    // draw
    $buf = '<div id="share-box" class="share-sidebar">
              <div id="donate">
                <form id="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">
                  <input type="hidden" name="cmd" value="_s-xclick" />
                  <input type="hidden" name="hosted_button_id" value="YVG8NLZ2QH34Y" />
                  <input type="image" src="' . BASE_URL . '/img/paypal.png" name="submit" alt="' . _('Support Commit-Digest using PayPal') . '" title="' . _('Support Commit-Digest using PayPal') . '" />
                  <img src="https://www.paypal.com/en_GB/i/scr/pixel.gif" alt="" />
                </form>

                <div id="flattr">&nbsp;</div>
              </div>

              <script type="text/javascript" src="http://api.flattr.com/js/0.5.0/load.js"></script>
              <script type="text/javascript">
                var theUrl         = "' . $theUrl . '";
                var theTitle       = "' . $theTitle . '";
                var theDescription = "' . $theDescription . '";
                ' .
                Cache::getMinInlineJs($script, 'donate') .
           '  </script>

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