<?php
require __DIR__ . '/vendor/autoload.php';

$config_file = __DIR__ . '/config.sample.php';
if (file_exists(__DIR__ . '/config.inc.php')) {
    $config_file = __DIR__ . '/config.inc.php';
}

require_once $config_file;

\SiteMaster\Core\Util::connectDB();

\SiteMaster\Core\User\Session::start();

require_once(__DIR__ . '/init_plugins.php');