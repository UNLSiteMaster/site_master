<?php
namespace SiteMaster\Core\Scan\Logger;

use DOMXPath;
use Monolog\Logger;
use SiteMaster\Core\Util;

class Basic extends \Spider_LoggerAbstract
{
    /**
     * @var bool|\Spider
     */
    protected $spider = false; 
    
    function __construct(\Spider $spider)
    {
        $this->spider = $spider;
    }
    
    public function log($uri, $depth, DOMXPath $xpath)
    {
        //Util::log(Logger::INFO, 'Logged: ' . $uri);
        $pages = $this->spider->getCrawlableUris($this->spider->getStartBase(), \Spider::getURIBase($uri), $uri, $xpath);
        
        foreach ($pages as $uri) {
            $pheanstalk = new \Pheanstalk_Pheanstalk('0.0.0.0');
            
            $data = array(
                'controller'=>'scan-page',
                'data' => array(
                    'uri'=>$uri,
                    'scanned_page_id'=>0,
                    'crawl'=>1,
                    'update'=>false
                )
            );

            $task_id = $pheanstalk->useTube('testtube')->put(json_encode($data));
        }
    }
}
