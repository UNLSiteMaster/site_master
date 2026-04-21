<?php
echo '===== Installing... =====' . PHP_EOL;

echo '===== Creating composer.json and installing base composer libraries =====' . PHP_EOL;
echo 'This could take awhile...' . PHP_EOL;
$root = dirname(__DIR__);

//Update composer
passthru('php ' . $root. '/composer.phar install -n');

require_once($root. '/scripts/update_libs.php');

//Run Update Script
echo '===== Running scripts/update.php to install database and plugins =====' . PHP_EOL;

require_once($root. '/scripts/update.php');

