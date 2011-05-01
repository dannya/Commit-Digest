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


// define database settings
define('DB_HOST',           'localhost');
define('DB_USER',           'root');
define('DB_PASSWORD',       'hello1');
define('DB_DATABASE',       'enzyme');


// ------- YOU SHOULDN'T NEED TO MODIFY BELOW HERE --------


// define app constants
define('APP_ID',            'commit-digest');
define('APP_NAME',          'KDE Commit-Digest');
define('VERSION',           '1.07');

define('META_AUTHOR',       'Danny Allen');
define('META_DESCRIPTION',  'A weekly overview of the development activity in KDE.');
define('META_KEYWORDS',     'kde, commit-digest, danny allen, dannya, plasma, akonadi, decibel, oxygen, solid, phonon, strigi');


if (empty($_SERVER['DOCUMENT_ROOT'])) {
  define('COMMAND_LINE',    true);
  define('BASE_DIR',        dirname(__FILE__));
} else {
  define('BASE_DIR',        rtrim($_SERVER['DOCUMENT_ROOT'], '/'));
  define('COMMAND_LINE',    false);
}


// define caching settings
define('CACHE_DIR',         BASE_DIR . '/cache/');

$cacheOptions = array('caching'             => false,
                      'cacheDir'            => CACHE_DIR,
                      'lifetime'            => 3600,
                      'fileNameProtection'  => true,
                      'writeControl'        => true,
                      'readControl'         => false,
                      'readControlType'     => 'md5');


if (COMMAND_LINE) {
  // set command line vars (error reporting, etc)
  error_reporting(E_ALL);
  define('LIVE_SITE', null);

} else {
  // set general site vars
  error_reporting(E_ALL|E_STRICT);

  define('BASE_URL',  'http://' . $_SERVER['HTTP_HOST']);

  // start user session
  session_start();

  // set environment (live / development)
  if ($_SERVER['HTTP_HOST'] == 'digest') {
    define('LIVE_SITE', false);
  } else {
    define('LIVE_SITE', true);
  }

  // path and ID for Piwik installation
  define('WEBSTATS_URL',  'allmyfriendsarecakes.com/piwik');
  define('WEBSTATS_ID',   6);
}


// set live site vars
if (LIVE_SITE) {
  ini_set('display_errors', false);
  ini_set('log_errors', true);
} else {
  ini_set('display_errors', true);
  ini_set('log_errors', false);
}


if (COMMAND_LINE) {
  ini_set('display_errors', true);

  // autoload doesn't work...
  function autoload($classes) {
    if (!is_array($classes)) {
      $classes = array($classes);
    }

    foreach ($classes as $class) {
      include('classes/' . $class . '.php');
    }
  }

} else {
  // add class dir to include path
  $classDirs = array(BASE_DIR . '/classes/shared/',
                     BASE_DIR . '/classes/specific/',
                     BASE_DIR . '/classes/ext/',
                     BASE_DIR . '/classes/ext/cacheLite/',
                     BASE_DIR . '/classes/ui/');

  set_include_path(get_include_path() . PATH_SEPARATOR . implode(PATH_SEPARATOR, $classDirs));

  // define autoloader
  spl_autoload_register();
}


// connect to database
Db::connect();


// load settings
$settings = Enzyme::loadSettings(true);

if (!$settings) {
  // show message about configuration
  echo Ui::drawHtmlPage(_('Enzyme backend instance needs to be configured.'),
                        _('Setup'),
                        array('/css/common.css'));
  exit;

} else {
  // define settings for app access
  foreach ($settings as $setting) {
    define($setting['setting'], $setting['value']);
  }
}


// set language
App::setLanguage();

// set timezone
date_default_timezone_set(DEFAULT_TIMEZONE);

?>