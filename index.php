<?php

/** @var \Composer\Autoload\ClassLoader $autoLoader */
$autoLoader = require_once 'vendor/autoload.php';
$autoLoader->addPsr4('m8rge\\OCF\\', '');

if ($argc == 2 && !empty($argv[1])) {
    $action = $argv[1];
    $ocf = new \m8rge\OCF\ServiceCheck();
    $ocf->run($action);
} else {
    exit(\m8rge\OCF\ServiceCheck::OCF_ERR_ARGS);
}
