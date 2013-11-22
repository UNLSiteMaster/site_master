<?php

namespace SiteMaster\Plugin;

class PluginListener
{
    protected $plugin;

    function __construct(PluginInterface $plugin)
    {
        $this->plugin = $plugin;
    }
}