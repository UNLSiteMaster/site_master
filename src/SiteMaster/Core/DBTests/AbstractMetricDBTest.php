<?php
namespace SiteMaster\Core\DBTests;

use SiteMaster\Core\Auditor\Metrics;
use SiteMaster\Core\Auditor\Site\Pages\Queued;
use SiteMaster\Core\Config;
use SiteMaster\Core\Plugin\PluginManager;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class DBTestCase
 * @package SiteMaster
 */
abstract class AbstractMetricDBTest extends DBTestCase
{
    const INTEGRATION_TESTING_URL = 'http://unlsitemaster.github.io/test_site/';
    
    protected static $originalPlugins = '[]';

    public function installBaseDB()
    {
        //Install base data
        parent::installBaseDB();

        //Now, install this plugin
        $plugin = $this->getPlugin();

        $plugins = array_merge(
            Config::get('PLUGINS'),
            array(
                $plugin->getMachineName() => array(),
            )
        );

        Config::set('PLUGINS', $plugins);

        //Install this metric
        if (!$plugin->isInstalled()) {
            $plugin->performUpdate();

            PluginManager::initialize(
                new EventDispatcher(),
                array(
                    'internal_plugins' => array(
                        'Core' => array(),
                    ),
                    'external_plugins' => Config::get('PLUGINS')
                ),
                true //force re-initialize
            );
        }

        PluginManager::getManager()->initializeMetrics();
    }

    protected function runScan()
    {
        //Create a mock worker to scan it
        $keep_scanning = true;
        while ($keep_scanning) {
            //Get the queue
            $queue = new Queued();

            if (!$queue->count()) {
                $keep_scanning = false;

                //Check again.
                continue;
            }

            /**
             * @var $page Page
             */
            $queue->rewind();
            $page = $queue->current();

            $page->scan();

            sleep(1);
        }
    }
    
    /**
     * Get the plugin object for this metric
     *
     * @return \SiteMaster\Core\Plugin\PluginInterface
     */
    abstract function getPlugin();
    
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        //Store the original plugins so that we can revert back to them
        self::$originalPlugins = Config::get('PLUGINS');
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        //Reset to the original plugins
        Config::set('PLUGINS', self::$originalPlugins);
    }
}