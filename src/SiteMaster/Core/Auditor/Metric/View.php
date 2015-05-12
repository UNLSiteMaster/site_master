<?php
namespace SiteMaster\Core\Auditor\Metric;

use SiteMaster\Core\Auditor\Metric;
use SiteMaster\Core\Auditor\Metrics;
use SiteMaster\Core\Config;
use SiteMaster\Core\Plugin\PluginManager;
use SiteMaster\Core\UnexpectedValueException;
use SiteMaster\Core\ViewableInterface;

class View implements ViewableInterface, \Savvy_Turbo_CacheableInterface
{
    protected $metric;
    
    protected $options;
    
    public function __construct(array $options)
    {
        if (!isset($options['metrics_id'])) {
            throw new UnexpectedValueException('Please provide a metric id', 404);
        }
        
        if (!$this->metric = Metric::getByID($options['metrics_id'])) {
            throw new UnexpectedValueException('Unknown metric', 404);
        }
        
        $this->options = $options;
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

    public function getCacheKey()
    {
        return 'metric-view-' . $this->metric->id . '-format-' . $this->options['format'];
    }

    public function run()
    {
        //Nothing to do
    }

    public function preRun($cached)
    {
        //Nothing to do
    }
}