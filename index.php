<?php

/** @var \Composer\Autoload\ClassLoader $autoLoader */
$autoLoader = require_once 'vendor/autoload.php';
$autoLoader->addPsr4('m8rge\\', 'src');

if (!empty($argv[1])) {
    $action = $argv[1];
    $ocf = new \m8rge\OCF\ServiceCheck();
    $ocf->run($action);
}
