<?php
namespace SiteMaster\Plugin;

use SiteMaster\Core\Plugin\PluginManager;

class PluginManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function getPluginNamespaceFromName()
    {
        $plugin_manager = PluginManager::getManager();
        
        $this->assertEquals('Example', $plugin_manager->getPluginNamespaceFromName('example'));
        $this->assertEquals('example', $plugin_manager->getPluginNamespaceFromName('Example'));

        $this->assertEquals('Core', $plugin_manager->getPluginNamespaceFromName('core'));
        $this->assertEquals('Core', $plugin_manager->getPluginNamespaceFromName('Core'));
    }
}