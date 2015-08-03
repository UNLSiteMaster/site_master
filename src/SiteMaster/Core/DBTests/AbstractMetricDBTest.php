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

    protected function setUp()
    {
        parent::setUp();
        
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
    }

    protected function tearDown()
    {
        parent::setUp();

        $plugin = $this->getPlugin();

        //uninstall this metric
        if ($plugin->isInstalled()) {
            $plugin->uninstall();
        }
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

    public function setUpDB()
    {
        $this->cleanDB();
        $this->installBaseDB();
        $this->installMockData(new BaseTestDataInstaller());
    }

    /**
     * Get the plugin object for this metric
     * 
     * @return \SiteMaster\Core\Plugin\PluginInterface
     */
    abstract function getPlugin();
}