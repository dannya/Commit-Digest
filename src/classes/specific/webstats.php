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


class Webstats {
  public static function track() {
    if (WEBSTATS_TYPE === false) {
      $buf = null;

    } else if (WEBSTATS_TYPE == 'google') {
      // google analytics
      $buf = '<script>
                (function(i,s,o,g,r,a,m){i["GoogleAnalyticsObject"]=r;i[r]=i[r]||function(){
                (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
                })(window,document,"script","http://www.google-analytics.com/analytics.js","ga");

                ga("create", "' . WEBSTATS_ID . '", "commit-digest.org");
                ga("send", "pageview");
             </script>';

    } else {
      throw new Exception('WEBSTATS_TYPE unset in ' . __CLASS__ . '::' . __METHOD__);
    }

    return $buf;
  }
}

?>