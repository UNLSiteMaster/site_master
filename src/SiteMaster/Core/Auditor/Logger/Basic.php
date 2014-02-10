<?php
namespace SiteMaster\Core\Auditor\Logger;

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
        Util::log(Logger::INFO, 'Logged: ' . $uri);
    }
}
