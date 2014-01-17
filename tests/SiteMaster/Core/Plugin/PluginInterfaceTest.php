<?php
namespace SiteMaster\Core\Plugin;

class PluginInterfaceTest extends \PHPUnit_Framework_TestCase
{
    public function testGetMachineName()
    {
        $plugin = new \SiteMaster\Core\Plugins\Example\Plugin();
        $this->assertEquals('example', $plugin->getMachineName());

        $plugin = new \SiteMaster\Core\Plugin\Plugin();
        $this->assertEquals('plugin', $plugin->getMachineName());

        $plugin = new \SiteMaster\Registry\Plugin();
        $this->assertEquals('registry', $plugin->getMachineName());
    }

    public function testGetPluginType()
    {
        $plugin = new \SiteMaster\Core\Plugins\Example\Plugin();
        $this->assertEquals('external', $plugin->getPluginType());

        $plugin = new \SiteMaster\Core\Plugin();
        $this->assertEquals('internal', $plugin->getPluginType());
    }

    public function testIsExternal()
    {
        $plugin = new \SiteMaster\Plugins\Example\Plugin();
        $this->assertEquals(true, $plugin->isExternal());

        $plugin = new \SiteMaster\Core\Plugin();
        $this->assertEquals(false, $plugin->isExternal());
    }
}