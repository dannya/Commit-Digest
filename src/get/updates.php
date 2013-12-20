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


// define specifics
$dateFormat = 'Y-m-d\TH:i:sP';
$cacheKey = 'rss';
$updateUrl = BASE_URL . '/updates/';

if (isset($_GET['full'])) {
  $cacheKey .= '_full';
  $updateUrl .= 'full/';
}


// check if we have generated RSS stored in cache?
if (LIVE_SITE) {
  $buf = Cache::load($cacheKey);

  if ($buf) {
    // we have cached RSS, output that
    echo $buf;
    exit;
  }
}


// set RSS header
header("Content-type: text/xml");


// load last 5 issues
$digests = Digest::loadDigests('issue', 'latest', true, 5);


// draw header stuff
$buf = '<?xml version="1.0" encoding="UTF-8"?>
        <feed xml:lang="en" xmlns="http://www.w3.org/2005/Atom">
          <title>' . Config::getSetting('enzyme', 'PROJECT_NAME') . '</title>
          <updated>' . date($dateFormat) . '</updated>
          <description>' . Config::$meta['description'] . '</description>
          <language>en</language>
          <id>' . $updateUrl . '</id>
          <link type="text/html" href="' . BASE_URL . '/" rel="alternate"/>' . "\n";

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

  // draw item...
  $itemUrl = BASE_URL . '/' . $linkDir . '/' . $digest['date'] . '/';
  $itemDate = date($dateFormat, strtotime($digest['date']));

  // load full digest data?
  $html = '';

  if (isset($_GET['full'])) {
    // show synopsis and statistics
    $fullDigest = new IssueUI('issues', $digest['date']);

    $content =
      $fullDigest->container('intro', $fullDigest->drawIntro()) .
      $fullDigest->container('stats', $fullDigest->drawStatistics(false));

  } else {
    // only show synopsis (default)
    $content = $digest['synopsis'];
  }

  $buf .=  '<entry>
              <title>' . sprintf(_('Issue %d: %s'), $digest['id'], Date::get('full', $digest['date'])) . '</title>
              <published>' . $itemDate . '</published>
              <updated>' . $itemDate . '</updated>
              <author>
                <name>' . $authors[$digest['author']] . '</name>
              </author>
              <content type="html">' . htmlspecialchars($content) . '</content>
              <link type="text/html" rel="alternate" href="' . $itemUrl . '" />
              <id>' . $itemUrl . '</id>' .
              $comments .
           '</entry>' . "\n";

  ++$counter;
}

// draw footer
$buf .=  '  </feed>';


// add to cache (store for 1 hour before regeneration)
Cache::save($cacheKey, $buf, false, 3600);


// output and finish
echo $buf;
exit;

?>