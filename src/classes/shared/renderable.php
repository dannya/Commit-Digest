<?php

/*-------------------------------------------------------+
 | KDE Commit-Digest
 | Copyright 2013 Danny Allen <danny@commit-digest.org>
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


abstract class Renderable {
  public function render($tokens = array()) {
    // determine theme location
    if (isset(Config::$theme) && Config::$theme[0] !== 'default') {
        $theme = Config::$theme[0];
    } else {
        $theme = 'default';
    }

    // define template engine options
    $options = array();
    if (LIVE_SITE) {
      $options = array(
        'cache' => BASE_DIR . '/templates/' . $theme . '/cache',
      );
    }

    // initialise templating engine
    $loader = new Twig_Loader_Filesystem(BASE_DIR . '/templates/' . $theme);
    $twig = new Twig_Environment($loader, $options);

    // enable additional Twig extensions
    $twig->addExtension(new Twig_Extensions_Extension_I18n());
    $twig->addExtension(new Twig_Extensions_Extension_Text());

    // define global tokens
    $global_tokens = array(
      'BASE_URL'         => BASE_URL,
      'PROJECT_NAME'     => Config::getSetting('enzyme', 'PROJECT_NAME'),
    );

    // return rendered template output
    return $twig->render($this->id . '.html', array_merge($global_tokens, $tokens));
  }
}

?>