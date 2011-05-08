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


class DigestUi {
  public $frame             = null;

  private $title            = APP_NAME;

  private $style            = array('/css/common.css');
  private $appScript        = array('/js/prototype.js',
                                    '/js/effects.js');

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

    } else if ($current == 'commit-spy') {
      $this->frame = new CommitSpyUi();

    } else if ($current == 'contribute') {
      $this->frame = new ContributeUi();

    } else if ($current == 'options') {
      $this->frame = new OptionsUi();

    } else if ($current == 'data') {
      $this->frame = new DataUi();

    } else {
      $this->frame = new IndexUi();
    }

    // get specific style
    $this->style = array_merge($this->style, $this->frame->getStyle());

    // set script
    $this->userScript[] = '/js/index.php?script=common&amp;id=' . $this->frame->id;
  }


  public function drawTitle() {
    $buf = '<title>' . APP_NAME . ' - ' . $this->frame->title . '</title>';

    return $buf;
  }


  public function drawMeta() {
    if (defined('META_AUTHOR')) {
      $buf = '<meta name="author" content="' . META_AUTHOR . '" />';
    } else {
      $buf = null;
    }

    $buf .=  '<meta name="description" content="' . META_DESCRIPTION . '" />
              <meta name="keywords" content="' . META_KEYWORDS . '" />
              <link rel="shortcut icon" href="' . BASE_URL .'/favicon.ico" type="image/x-icon" />
              <link rel="icon" href="' . BASE_URL . '/favicon.ico" type="image/x-icon" />
              <link rel="alternate" type="application/rss+xml" title="" href="' . BASE_URL . '/updates/" />';

    return $buf;
  }


  public function drawStyle() {
    $buf = null;

    foreach ($this->style as $style) {
      $buf .= '<link rel="stylesheet" href="' . BASE_URL . $style . '?version=' . VERSION . '" type="text/css" media="screen" />' . "\n";
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
                    $("header-review-target").src = "' . ENZYME_URL . '/get/publish.php?date=" + date + "&state=" + state;

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
    $buf  .= '<div id="header">
                <div id="header-bar">
                  <div id="logo" onclick="top.location=\'' . BASE_URL . '/\';">&nbsp;</div>
                </div>

                <div id="language-selector">' .
                  Ui::htmlSelector('language', Digest::getLanguages(), LANGUAGE, 'changeLanguage(event);') .
             '  </div>
              </div>';

    return $buf;
  }


  public function drawSidebar() {
    // draw
    $buf = '<div id="sidebar">
              <a id="sidebar-logo" class="n" style="display:none;" href="' . BASE_URL . '/">
                &nbsp;
              </a>

              <ul>
                <li>
                  <a href="' . BASE_URL . '/" title="' . _('Front Page') . '">' . _('Front Page') . '</a>
                </li>

                <li class="spacer">
                  <a href="' . BASE_URL . '/issues/" title="' . _('Issues') . '">' . _('Issues') . '</a>
                </li>
                <li>
                  <ul>
                    <li>
                      <a href="' . BASE_URL . '/issues/latest/" title="' . _('Latest Issue') . '">' . _('Latest Issue') . '</a>
                    </li>
                  </ul>
                </li>

                <li class="spacer">
                  <a href="' . BASE_URL . '/archive/" title="' . _('Archive') . '">' . _('Archive') . '</a>
                </li>

                <li class="spacer">
                  <a href="' . BASE_URL . '/six-months-ago/" title="' . _('Six Months Ago') . '">' . _('Six Months Ago') . '</a>
                </li>
                <li>
                  <a href="' . BASE_URL . '/one-year-ago/" title="' . _('One Year Ago') . '">' . _('One Year Ago') . '</a>
                </li>
                <li>
                  <a href="' . BASE_URL . '/issues/random/" title="' . _('Random Digest') . '">' . _('Random Digest') . '</a>
                </li>

                <li class="spacer">
                  <a href="' . BASE_URL . '/commit-spy/" title="' . _('Commit Spy') . '">' . _('Commit Spy') . '</a>
                </li>
                <li>
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


  public function drawFooter() {
    $buf = '<div id="footer">' .
              sprintf(_('%s by <a href="mailto:%s">%s</a>, %s'), PROJECT_NAME, 'danny@commit-digest.org', 'Danny Allen', '2006-2011') .
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
    $button['digg']     = 'http://www.addtoany.com/add_to/digg?linkurl=' . $theUrlEncode . '&amp;type=page&amp;linkname=' . $theTitleEncode;
    $button['twitter']  = 'http://www.addtoany.com/add_to/twitter?linkurl=' . $theUrlEncode . '&amp;type=page&amp;linkname=' . $theTitleEncode;
    $button['facebook'] = 'http://www.addtoany.com/add_to/facebook?linkurl=' . $theUrlEncode . '&amp;type=page&amp;linkname=' . $theTitleEncode;


    // define script
    $script =  'document.observe("dom:loaded", function() {
                  // check if we have space for elements
                  Event.observe(window, "resize", function() {
                    if (document.viewport.getDimensions().height < 560) {
                      $("share-box").writeAttribute("class", "share-bottom");
                    } else {
                      $("share-box").writeAttribute("class", "share-sidebar");
                    }
                  });

                  // hide elements based on initial size?
                  if (document.viewport.getDimensions().height < 550) {
                    $("share-box").writeAttribute("class", "share-bottom");

                    // set flattr button size
                    var flattrButton = "compact";

                  } else {
                    $("share-box").writeAttribute("class", "share-sidebar");

                    // set flattr button size
                    if (document.viewport.getDimensions().height < 600) {
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

                  // fade out share / donate section?
                  if ($("share-box").hasClassName("share-sidebar")) {
                    new Effect.Fade("share-box", { duration:0.5,
                                                   from:1,
                                                   to:0.5,
                                                   delay:5 });
                  }
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
                <a id="button-rss" class="button" target="_blank" href="' . $button['rss'] . '" title="' . sprintf(_('Subscribe to %s updates'), PROJECT_NAME) . '">&nbsp;</a>
                <a id="button-email" class="button" target="_blank" href="' . $button['email'] . '" title="' . _('Send this issue by email...') . '">&nbsp;</a>
                <a id="button-twitter" class="button" target="_blank" href="' . $button['twitter'] . '" title="' . _('Share this issue by Twitter...') . '">&nbsp;</a>
                <a id="button-facebook" class="button" target="_blank" href="' . $button['facebook'] . '" title="' . _('Share this issue by Facebook...') . '">&nbsp;</a>
                <a id="button-digg" class="button" target="_blank" href="' . $button['digg'] . '" title="' . _('Digg this issue...') . '">&nbsp;</a>
              </div>
            </div>';

    return $buf;
  }
}

?>