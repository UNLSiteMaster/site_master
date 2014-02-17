<?php
ini_set('display_errors', true);

//Initialize all settings and autoloaders
require_once(__DIR__ . "/../init.php");

while (true) {
    //Get the queue
    $queue = new SiteMaster\Core\Auditor\Site\Pages\Queued();
    
    if (!$queue->count()) {
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

    $page->scan();
    
    sleep(1);
}
