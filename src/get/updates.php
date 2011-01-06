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
            <title>KDE Commit-Digest Updates</title>
            <link>' . BASE_URL . '/</link>
            <description>A weekly overview of the development activity in KDE.</description>
            <language>en</language>
            <atom:link href="' . BASE_URL . '/updates/" rel="self" type="application/rss+xml" />';

//// add call for volunteers
//$buf  .= '<item>
//            <title>KDE Commit-Digest Needs You!</title>
//            <link>http://enzyme.commit-digest.org/</link>
//            <guid>http://enzyme.commit-digest.org/</guid>
//            <dc:creator>Danny Allen</dc:creator>
//            <description><![CDATA[The KDE Commit-Digest is relaunching with a new distributed participant model, which means that you can volunteer to help produce the weekly Digest!<br /><br />There are <a href="http://enzyme.commit-digest.org/">jobs in 4 main areas available</a>:<br /><br /><b><u><a href="http://enzyme.commit-digest.org/#reviewer">Commit Reviewer</a></u></b><br />Commit Reviewers look at all the recent commits, selecting those which are significant and interesting enough to be included into the weekly Commit-Digest.<br /><br /><b><u><a href="http://enzyme.commit-digest.org/#classifier">Commit Classifier</a></u></b><br />Commit Classifiers sort the selected commits into areas (which is partly automated), and by type (such as bug fix, feature, etc).<br /><br /><b><u><a href="http://enzyme.commit-digest.org/#editor">Feature Editor</a></u></b><br />Feature Editors contact people working on interesting projects and assist them in writing original pieces which are presented in the introduction of each Commit-Digest.<br /><br /><b><u><a href="http://enzyme.commit-digest.org/#translator">Translator</a></u></b><br />Translators increase the reach of the Commit-Digest and the work done across the project by making the weekly Commit-Digests (and the website interfaces) available in the native language of people around the world.<br /><br />The KDE Commit-Digest can only return if there are enough volunteers, so please think about joining if you can spare some time out of each week.<br /><br />Thanks for your contribution,<br />Danny]]></description>
//            <pubDate>Sun, 03 Oct 2010 12:00:00 +0000</pubDate>
//          </item>' . "\n";

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