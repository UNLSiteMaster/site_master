<?php
namespace SiteMaster\Core\Auditor\Metrics;

use SiteMaster\Core\Auditor\Metrics;
use SiteMaster\Core\Config;
use SiteMaster\Core\ViewableInterface;

class View extends Metrics implements ViewableInterface
{
    public function __construct() {
        parent::__construct(false);
    }
    
    public function getURL()
    {
        return Config::get('URL') . 'metrics/';
    }

    public function getPageTitle()
    {
        return 'Metrics';
    }
}