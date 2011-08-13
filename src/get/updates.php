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


include($_SERVER['DOCUMENT_ROOT'] . '/autoload.php');


// track request
Webstats::manual(array('title' => 'Blog RSS'));


// check if we have generated RSS stored in cache
$buf = Cache::load('rss');

// set RSS header
header("Content-type: text/xml");


// load last 5 issues
$digests = Digest::loadDigests('issue', 'latest', true, 5);


// draw header stuff
$buf = '<?xml version="1.0" encoding="utf-8"?>
        <rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xml:base="' . BASE_URL . '/" xmlns:dc="http://purl.org/dc/elements/1.1/">
          <channel>
            <title>' . sprintf(_('%s Updates'), Config::getSetting('enzyme', 'PROJECT_NAME')) . '</title>
            <link>' . BASE_URL . '/</link>
            <description>' . _('A weekly overview of the development activity in KDE.') . '</description>
            <language>en</language>
            <atom:link href="' . BASE_URL . '/updates/" rel="self" type="application/rss+xml" />';

// draw posts
$counter = 0;

foreach ($digests as $digest) {
  // show comments URL?
  if (!empty($digest['comments'])) {
    $comments = '<comments>' . $digest['comments'] . '</comments>';
  } else {
    $comments = null;
  }

  // get author name
  if (!isset($authors[$digest['author']])) {
    $authors[$digest['author']] = Digest::formatName(Digest::getAuthorDetails($digest['author']));
  }

  // set link directory
  if ($digest['type'] == 'archive') {
    $linkDir = 'archive';
  } else {
    $linkDir = 'issues';
  }

  // draw item
  $itemUrl = BASE_URL . '/' . $linkDir . '/' . $digest['date'] . '/';

  $buf .=  '<item>
              <title>' . sprintf(_('Issue %d: %s'), $digest['id'], Date::get('full', $digest['date'])) . '</title>
              <link>' . $itemUrl . '</link>
              <guid>' . $itemUrl . '</guid>
              <dc:creator>' . $authors[$digest['author']] . '</dc:creator>
              <description>' . enclose($digest['synopsis']) . '</description>' .
              $comments .
           '  <pubDate>' . date('D, j M Y h:i:s', strtotime($digest['date'])) . ' +0000</pubDate>
            </item>' . "\n";

  ++$counter;
}

// draw footer
$buf .=  '  </channel>
          </rss>';


// add to cache (store for 1 hour before regeneration)
Cache::save('rss', $buf, false, 3600);


// output and finish
echo $buf;
exit;



// utility functions
function enclose($string) {
  return '<![CDATA[' . $string . ']]>';
}


?>