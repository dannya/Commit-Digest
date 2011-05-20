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


class ContributeUi {
  public $id      = 'contribute';
  public $title   = null;

  private $jobs   = array();


  public function __construct() {
    // set title
    $this->title = _('Contribute');

    // get list of available jobs
    $this->jobs  = Enzyme::getAvailableJobs();
  }


  public function draw() {
    $buf = '<h1>' . $this->title . '</h1>

            <p>' .
              sprintf(_('There are many ways to help the %s by contributing your time, effort, or money...'), PROJECT_NAME) .
           '</p>

            <div class="column">
              <h2>' . _('Time') . '</h2>';

    // draw jobs
    foreach ($this->jobs as $job => $jobData) {
      $buf  .= '<h3>' .
                  $jobData['title'] .
               '  <span>
                    <input type="button" onclick="top.location=\'' . ENZYME_URL . '/#' . $job . '\';" value="' . _('Apply!') . '" />
                  </span>
                </h3>
                <p>' .
                  $jobData['description'] .
               '</p>';
    }

    $buf  .= '</div>


            <div class="column">
              <h2>' . _('Money') . '</h2>
              <p>' .
                sprintf(_('If you are unable to help the %s by donating your time and effort, you can still show your appreciation and support the ongoing work by donating money.'), PROJECT_NAME) .
           '  </p>

              <div id="donate-box">
                <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
                  <input type="hidden" name="cmd" value="_s-xclick" />
                  <input type="hidden" name="hosted_button_id" value="YVG8NLZ2QH34Y" />
                  <input type="image" src="' . BASE_URL . '/img/paypal.png" name="submit" alt="' . _('Support Commit-Digest using PayPal') . '" title="' . _('Support Commit-Digest using PayPal') . '" />
                  <img src="https://www.paypal.com/en_GB/i/scr/pixel.gif" alt="" />
                </form>

                <a href="http://flattr.com/thing/65789/KDE-Commit-Digest-Contribute" target="_blank">
                  <img src="http://api.flattr.com/button/button-static-50x60.png" alt="" />
                </a>
              </div>

              <h2>' . _('And Finally...') . '</h2>
              <p>' .
                sprintf(_('Even if you can\'t contribute time, effort, or money in support, your enjoyment of the %s is contribution enough, and leaves us with a warm, fuzzy feeling inside!'), PROJECT_NAME) .
           '    <br /><br />' .
                sprintf(_('Spread the word about KDE and the Commit-Digest by using the %s"share" links%s in the sidebar of each Digest.'), '<a href="#" onclick="highlightShareBox(event);">', '</a>') .
           '    <br /><br />' .
                sprintf(_('Let us know your thoughts about our work at %shello@commit-digest.org%s'), '<br /><a href="mailto:hello@commit-digest.org">', '</a>') .
           '  </p>
            </div>';

    // draw share / donate box
    $theUrl         = BASE_URL . '/contribute/';
    $theTitle       = PROJECT_NAME . ' - Contribute';
    $theDescription = 'Support the work of the ' . PROJECT_NAME . '.';

    $buf .= DigestUi::drawShareBox($theUrl, $theTitle, $theDescription);

    return $buf;
  }


  public function getScript() {
    return array();
  }


  public function getStyle() {
    return array('/css/contributeui.css');
  }
}

?>