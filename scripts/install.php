<?php
echo '===== Installing... =====' . PHP_EOL;

echo '===== Creating composer.json and installing base composer libraries =====' . PHP_EOL;
echo 'This could take awhile...' . PHP_EOL;
$root = dirname(__DIR__);
$json = file_get_contents($root . '/base_composer.json');
file_put_contents($root . '/composer.json', $json);

//Update composer
echo shell_exec('php ' . $root. '/composer.phar update');

require_once($root. '/scripts/update_libs.php');

//Run Update Script
echo '===== Running scripts/update.php to install database and plugins =====' . PHP_EOL;

require_once($root. '/scripts/update.php');

