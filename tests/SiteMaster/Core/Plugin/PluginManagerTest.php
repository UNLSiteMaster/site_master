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
        
        $this->assertEquals('\\SiteMaster\\Plugins\\Example\\', $plugin_manager->getPluginNamespaceFromName('example'));
        $this->assertEquals('\\SiteMaster\\Plugins\\Example\\', $plugin_manager->getPluginNamespaceFromName('Example'));

        $this->assertEquals('\\SiteMaster\\Core\\', $plugin_manager->getPluginNamespaceFromName('core'));
        $this->assertEquals('\\SiteMaster\\Core\\', $plugin_manager->getPluginNamespaceFromName('Core'));
    }
}