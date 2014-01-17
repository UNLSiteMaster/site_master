<?php
namespace SiteMaster\Core\DBTests;

use SiteMaster\Core\DBTests\MockTestDataInstallerInterface;
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
            $this->markTestSkipped('Test database is not available, database tests were skipped');
        }
    }
    
    public function cleanDB()
    {
        $pluginManager = PluginManager::getManager();
        $installedPlugins = $pluginManager->getInstalledPlugins();
        
        //Clear the database
        foreach ($installedPlugins as $name=>$plugin) {
            //Don't actually uninstall, just preform uninstall logic defined by the plugin (which should remove all SQL)
            $plugin->onUninstall();
        }

        
    }
    
    public function installBaseDB()
    {
        $pluginManager = PluginManager::getManager();
        $installedPlugins = $pluginManager->getInstalledPlugins();
        
        //Reset database to a fresh install
        foreach ($installedPlugins as $name=>$plugin) {
            $plugin->onInstall();
        }
    }
    
    public function installMockData(MockTestDataInstallerInterface $installer)
    {
        $installer->install();
    }
}