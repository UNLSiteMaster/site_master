<?php
namespace SiteMaster\Core\DBTests;

use SiteMaster\Core\Plugin\PluginManager;
use SiteMaster\Core\Util;

/**
 * Class DBTestCase
 * @package SiteMaster
 */
class DBTestCase extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        try {
            Util::connectTestDB();
        } catch (\Exception $e) {
            $this->markTestSkipped('Test database is not available, database tests were skipped: ' . $e->getMessage());
        }
    }
    
    public function cleanDB()
    {
        $pluginManager = PluginManager::getManager();
        $installedPlugins = $pluginManager->getAllPlugins();
        
        //Clear the database
        foreach ($installedPlugins as $name=>$plugin) {
            $plugin = PluginManager::getManager()->getPluginInfo($plugin);
            //Don't actually uninstall, just perform uninstall logic defined by the plugin (which should remove all SQL)
            $plugin->onUninstall();
        }

        
    }
    
    public function installBaseDB()
    {
        $pluginManager = PluginManager::getManager();
        $installedPlugins = $pluginManager->getAllPlugins();
        
        //Reset database to a fresh install
        foreach ($installedPlugins as $name=>$plugin) {
            $plugin = PluginManager::getManager()->getPluginInfo($plugin);
            $plugin->onInstall();
        }
    }
    
    public function installMockData(MockTestDataInstallerInterface $installer)
    {
        $installer->install();
    }
}