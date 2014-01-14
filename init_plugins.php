<?php

//Register the plugin autoloader
spl_autoload_register('\SiteMaster\Plugin\PluginManager::autoload');

\SiteMaster\Plugin\PluginManager::initialize(
    new \Symfony\Component\EventDispatcher\EventDispatcher(),
    array(
        'internal_plugins' => array(
            'Core' => array(),
            'Home' => array(),
            'User' => array(),
            'Registry' => array(),
            'Plugin' => array(),
        ),
        'external_plugins' => \SiteMaster\Config::get('PLUGINS')
    )
);