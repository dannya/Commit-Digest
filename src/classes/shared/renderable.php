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
  private $twig             = null;
  private $global_tokens    = array();


  public function __construct() {
    $inst = self::instantiate();

    $this->twig = $inst['twig'];
    $this->global_tokens = $inst['global_tokens'];
  }


  public static function instantiate() {
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
      'ENZYME_URL'       => Config::getSetting('enzyme', 'ENZYME_URL'),
    );

    // return instance varaibles for the non-static context
    return array(
      'twig' => $twig,
      'global_tokens' => $global_tokens,
    );
  }


  public function render($tokens = array(), $template = null) {
    // if template is not specified, use ID of subclass
    if (!$template) {
        $template = $this->id;
    }

    // return rendered template output
    return $this->twig->render($template . '.html', array_merge($this->global_tokens, $tokens));
  }


  public static function render_static($tokens = array(), $template = null) {
    // instantiate in a static context
    $inst = Renderable::instantiate();

    // if template is not specified, use ID of subclass
    if (!$template) {
        $template = $this->id;
    }

    // return rendered template output
    return $inst['twig']->render($template . '.html', array_merge($inst['global_tokens'], $tokens));
  }
}

?>