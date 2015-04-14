<?php
namespace SiteMaster\Core\Auditor\Metric;

use SiteMaster\Core\Auditor\Metric;
use SiteMaster\Core\Auditor\Metrics;
use SiteMaster\Core\Config;
use SiteMaster\Core\Plugin\PluginManager;
use SiteMaster\Core\UnexpectedValueException;
use SiteMaster\Core\ViewableInterface;

class View implements ViewableInterface
{
    protected $metric;
    
    public function __construct(array $options)
    {
        if (!isset($options['metrics_id'])) {
            throw new UnexpectedValueException('Please provide a metric id', 404);
        }
        
        if (!$this->metric = Metric::getByID($options['metrics_id'])) {
            throw new UnexpectedValueException('Unknown metric', 404);
        }
    }
    
    public function getURL()
    {
        return Config::get('URL') . 'metrics/' . $this->metric->id . '/';
    }

    public function getPageTitle()
    {
        return 'Metric: ' . $this->metric->getMetricObject()->getName();
    }

    /**
     * @return \SiteMaster\Core\Auditor\Metric
     */
    public function getMetric()
    {
        return $this->metric;
    }
}