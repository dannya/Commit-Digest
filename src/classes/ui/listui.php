<?php

/*-------------------------------------------------------+
| KDE Commit-Digest
| Copyright 2010 Danny Allen <danny@commit-digest.org>
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


class ListUi {
  public $id          = null;
  public $title       = null;

  private $sortType   = null;

  private $sortAlt    = null;
  private $sortString = null;


  public function __construct($id) {
    // set id and title
    $this->id    = $id;

    if ($this->id == 'issues') {
      $this->title = _('Issues');
    } else if ($this->id == 'archive') {
      $this->title = _('Archive');
    }

    // set sort params
    if (isset($_REQUEST['sort']) && ($_REQUEST['sort'] == 'earliest')) {
      $this->sortType   = 'earliest';

      $this->sortAlt    = 'latest';
      $this->sortString = _('Sort by latest digest...');

    } else {
      $this->sortType   = 'latest';

      $this->sortAlt    = 'earliest';
      $this->sortString = _('Sort by earliest digest...');
    }


    // load items
    if ($this->id == 'archive') {
      $type = $this->id;
    } else {
      $type = 'issue';
    }

    $this->data = Cache::loadSave($type . '_' . $this->sortType, 'Digest::loadDigests', array($type, $this->sortType));
  }


  public function draw() {
    $buf = '<h1>' . $this->title . '</h1>
            <a id="sort" class="fade" href="?sort=' . $this->sortAlt . '">' . $this->sortString . '</a>';

    // show attribution?
    if ($this->id == 'archive') {
      $buf .= '<p id="attribution">' .
                sprintf(_('For %d issues, %s produced the KDE-CVS Digest. Here are the archives of his digests:'),
                        129,
                        '<a href="mailto:dkite@shaw.ca">Derek Kite</a>') .
              '</p>';
    }

    $buf .= '<div class="container">';

    foreach ($this->data as $digest) {
      $url = BASE_URL . '/' . $this->id . '/' . $digest['date'] . '/';

      // show issue number with date?
      if ($digest['type'] == 'archive') {
        $dateString = Date::get('full', $digest['date']);
      } else {
        $dateString = sprintf(_('Issue %d: %s'), $digest['id'], Date::get('full', $digest['date']));
      }

      $buf .=  '<div class="row">
                  <a class="date" href="' . $url . '">' .
                    $dateString .
               '  </a>
                  <a class="text filled" href="' . $url . '">' .
                    strip_tags($digest['synopsis']) .
               '  </a>
                </div>';
    }

    $buf .= '</div>';

    return $buf;
  }


  public function getScript() {
    return array();
  }


  public function getStyle() {
    return array('/css/listui.css');
  }
}

?>