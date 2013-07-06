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


class ListUi extends Renderable {
  public $id          = null;
  public $title       = null;

  private $sortType   = null;

  private $sortAlt    = null;
  private $sortString = null;


  public function __construct($id) {
    parent::__construct();

    // set id and title
    $this->id = $id;

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

    $this->data = Cache::loadSave(array('base'  => DIGEST_APP_ID,
                                        'id'    => $type . '_' . $this->sortType),
                                  'Digest::loadDigests',
                                  array($type,
                                        $this->sortType));
  }


  public function draw() {
    // define tokens
    $tokens = array(
      'title' => $this->title,
      'sort_alt' => $this->sortAlt,
      'sort_string' => $this->sortString,
      'issues' => $this->data,
    );

    // add additional tokens for archive/
    if ($this->id == 'archive') {
      $tokens = array_merge(
        $tokens,
        array(
          'archive_author' => 'Derek Kite',
          'archive_issues' => count($this->data),
        )
      );
    }

    return parent::render($tokens);
  }


  public function getScript() {
    return array();
  }


  public function getStyle() {
    return array('/css/frame/listui.css');
  }
}

?>