<?php

//Register the plugin autoloader
spl_autoload_register('\SiteMaster\Core\Plugin\PluginManager::autoload');

\SiteMaster\Core\Plugin\PluginManager::initialize(
    new \Symfony\Component\EventDispatcher\EventDispatcher(),
    array(
        'internal_plugins' => array(
            'Core' => array(),
        ),
        'external_plugins' => \SiteMaster\Core\Config::get('PLUGINS')
    ),
    \SiteMaster\Core\Config::get('GROUPS')
);
