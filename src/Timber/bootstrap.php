<?php

$debug = new \Phalcon\Debug();
$debug->listen();

// use the Phalcon Bootloader so we can load classes
$loader  = new \Phalcon\Loader();

// do we need this?
// $loader->registerDirs(['../app']);

// set_include_path ('/home/am/projects/Timber2/src');
// can this be shifted?
$loader->registerNamespaces(['Timber' => __DIR__], true);
$loader->register();

