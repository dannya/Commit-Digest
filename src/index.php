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


// use page caching? (only if set and on live site)
if (!empty($cacheOptions['caching']) && LIVE_SITE) {
  $cache = new Cache_Lite_Output($options);

  if ($cache->start($_SERVER['REQUEST_URI'])) {
    // page will be rendered from cache, so stop here!
    exit;
  }
}


// manage UI
$ui = new DigestUi();


// draw
if (!empty($ui->frame->onlyContent)) {
  // only draw frame content, not HTML
  echo $ui->drawContent();

} else {

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="en" lang="en">
  <head id="head">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <?php
      echo $ui->drawTitle();
      echo $ui->drawMeta();
      echo $ui->drawStyle();
      echo $ui->drawScript();
    ?>
  </head>

  <body id="body"<?php echo $ui->getBodyClasses(); ?>>
    <div id="content">
      <?php
        echo $ui->drawHeader();
        echo $ui->drawSidebar();
        echo $ui->drawContent();
        echo $ui->drawFooter();
      ?>
    </div>
<?php

  // finish page caching?
  if (isset($cache)) {
    $cache->end();
  }

?>
  </body>
</html>
<?php

}

?>