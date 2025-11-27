<?php
// function __autoload ($className) {
spl_autoload_register(function($className) {
  $baseDir = __DIR__ . '/classes/';
  $classFile = $baseDir . strtolower($className) . '.php';

  if (file_exists($classFile)) {
    require_once $classFile;
  }
});
?>

