<?php
ini_set('display_errors', true);

//Initialize all settings and autoloaders
require_once(__DIR__ . "/../init.php");

$config_file = __DIR__ . '/../config.inc.php';
$last_modified = filemtime($config_file);


//There should only be one worker running at a time.  If there are running jobs, remove them and try again...
$running = new SiteMaster\Core\Auditor\Site\Pages\Running();
foreach ($running as $page) {
    //Log it
    SiteMaster\Core\Util::log(Monolog\Logger::ALERT, 'Re-scheduling running job: pages.id=' . $page->id);
    
    if ($page->tries >= 3) {
        //Give up, and mark it as an error
        $page->markAsError();
        continue;
    }
    
    $page->rescheduleScan();
}

while (true) {
    clearstatcache(false, $config_file);
    //Check the last modified time to see if we need to load a new config
    if (filemtime($config_file) > $last_modified) {
        SiteMaster\Core\Util::log(Monolog\Logger::NOTICE, 'stopping daemon due to a change in config.inc.php');
        exit(10);
    }
    
    //Get the queue
    $queue = new SiteMaster\Core\Auditor\Site\Pages\Queued();
    
    if (!$queue->count()) {
        //Reset the cached robots.txt
        \Spider_Filter_RobotsTxt::$robotstxt = array();
        
        //Sleep for 10 seconds
        sleep(10);
        
        //Check again.
        continue;
    }

    /**
     * @var $page \SiteMaster\Core\Auditor\Site\Page
     */
    $queue->rewind();
    $page = $queue->current();
    $scan = $page->getScan();

    echo date("Y-m-d H:i:s"). " - scanning page.id=" . $page->id . PHP_EOL;
    $page->scan();
    
    //Check if there might have been some errors
    $scan->reload();
    if ($scan->start_time == $scan->end_time) {
        SiteMaster\Core\Util::log(Monolog\Logger::NOTICE, 'attempting to restart daemon due to a possible error (start and end times are the same)',
            array(
                'sites.id' => $page->sites_id,
                'scans.id' => $page->scans_id,
            )
        );
        exit(11);
    }
    
    sleep(1);
}
