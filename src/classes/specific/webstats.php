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
    if (!defined('WEBSTATS_TYPE') || !defined('WEBSTATS_ID')) {
      throw new Exception('Unset constant in ' . __CLASS__ . '::' . __METHOD__);
    }


    if (WEBSTATS_TYPE == 'piwik') {
      // piwik
      if (!defined('WEBSTATS_URL')) {
        throw new Exception('WEBSTATS_URL unset in ' . __CLASS__ . '::' . __METHOD__);
      }

      $buf = '<script type="text/javascript">
                var pkBaseURL = (("https:" == document.location.protocol) ? "https://' . WEBSTATS_URL . '/" : "http://' . WEBSTATS_URL . '/");
                document.write(unescape("%3Cscript src=\'" + pkBaseURL + "piwik.js\' type=\'text/javascript\'%3E%3C/script%3E"));

                try {
                  var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", ' . WEBSTATS_ID . ');
                  piwikTracker.trackPageView();
                  piwikTracker.enableLinkTracking();
                } catch( err ) {}
              </script>
              <noscript>
                <p>
                  <img src="' . PROTOCOL . WEBSTATS_URL . '/piwik.php?idsite=' . WEBSTATS_ID . '" style="border:0" alt="" />
                </p>
              </noscript>';

    } else if (WEBSTATS_TYPE == 'google') {
      // google analytics
      $buf = '<script type="text/javascript">
                var _gaq = _gaq || [];
                _gaq.push(["_setAccount", "' . WEBSTATS_ID . '"]);
                _gaq.push(["_setDomainName", "none"]);
                _gaq.push(["_setAllowLinker", true]);
                _gaq.push(["_trackPageview"]);

                (function() {
                  var ga = document.createElement("script"); ga.type = "text/javascript"; ga.async = true;
                  ga.src = ("https:" == document.location.protocol ? "https://ssl" : "http://www") + ".google-analytics.com/ga.js";
                  var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ga, s);
                })();
              </script>';

    } else {
      throw new Exception('WEBSTATS_TYPE unset in ' . __CLASS__ . '::' . __METHOD__);
    }

    return $buf;
  }




  // piwik
  public static function manual($params = null) {
    if (!defined('WEBSTATS_TYPE') || (WEBSTATS_TYPE != 'piwik')) {
      throw new Exception(__CLASS__ . '::' . __METHOD__ . ' can only be used if WEBSTATS_TYPE == "piwik"');
    }

    if (defined('WEBSTATS_URL') && defined('WEBSTATS_ID')) {
      $t = new PiwikTracker(WEBSTATS_ID, PROTOCOL . WEBSTATS_URL);

      // set params?
      $title = null;
      if (isset($params['title'])) {
        $title = $params['title'];
      }

      // set url?
      if (isset($params['url'])) {
        $t->setUrl($params['url']);
      }

      // track
      $t->doTrackPageView($title);
    }
  }




  // google analytics
  public static function image() {
    if (!defined('WEBSTATS_TYPE') || (WEBSTATS_TYPE != 'google')) {
      throw new Exception(__CLASS__ . '::' . __METHOD__ . ' can only be used if WEBSTATS_TYPE == "google"');
    }

    return '<img src="' . self::imageUrl() . '" onload="alert(\'boo\');" style="display:none;" />';
  }


  public static function imageUrl() {
    if (!defined('WEBSTATS_TYPE') || (WEBSTATS_TYPE != 'google')) {
      throw new Exception(__CLASS__ . '::' . __METHOD__ . ' can only be used if WEBSTATS_TYPE == "google"');
    }

    if (defined('WEBSTATS_ID')) {
      $url    = BASE_URL . '/get/ga.php?utmac=' . WEBSTATS_ID . '&utmn=' . rand(0, 0x7fffffff);

      // append query string to URL
      $query  = $_SERVER['QUERY_STRING'];
      $path   = $_SERVER['REQUEST_URI'];

      if (empty($_SERVER['HTTP_REFERER'])) {
        $referer = '-';
      } else {
        $referer = $_SERVER['HTTP_REFERER'];
      }

      $url   .= '&utmr=' . urlencode($referer);

      if (!empty($path)) {
        $url .= '&utmp=' . urlencode($path);
      }

      $url   .= '&guid=ON';

      // return URL
      return str_replace('&', '&amp;', $url);

    } else {
      throw new Exception('WEBSTATS_ID unset in ' . __CLASS__ . '::' . __METHOD__);
    }
  }
}

?>