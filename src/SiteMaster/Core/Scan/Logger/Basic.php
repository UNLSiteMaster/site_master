<?php
namespace SiteMaster\Core\Scan\Logger;

use DOMXPath;
use Monolog\Logger;
use SiteMaster\Core\Util;

class Basic extends \Spider_LoggerAbstract
{
    public function log($uri, $depth, DOMXPath $xpath)
    {
        Util::log(Logger::INFO, 'Logged: ' . $uri);
    }
}
