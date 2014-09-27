<?php

include($_SERVER['DOCUMENT_ROOT'] . '/autoload.php');

$themes = array('default', 'neverland');

foreach ($themes as $theme) {
  $templateDirectory = BASE_DIR . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $theme;

  // define template engine options
  $options = array(
    'cache' => $templateDirectory . DIRECTORY_SEPARATOR . 'cache',
    'auto_reload' => true,
  );

  // initialise templating engine
  $loader = new Twig_Loader_Filesystem($templateDirectory);
  $twig = new Twig_Environment($loader, $options);

  // enable additional Twig extensions
  $twig->addExtension(new Twig_Extensions_Extension_I18n());
  $twig->addExtension(new Twig_Extensions_Extension_Text());

  // iterate over all your templates
  foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($templateDirectory), RecursiveIteratorIterator::LEAVES_ONLY) as $file) {
    // force compilation
    if ($file->isFile()) {
      $twig->loadTemplate(str_replace($templateDirectory . DIRECTORY_SEPARATOR, '', $file));
    }
  }
}

?>