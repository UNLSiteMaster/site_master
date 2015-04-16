<?php
namespace SiteMaster\Core\Auditor\Metric\Mark;

use SiteMaster\Core\Auditor\Metric;
use SiteMaster\Core\Auditor\Metrics;
use SiteMaster\Core\Config;
use SiteMaster\Core\UnexpectedValueException;
use SiteMaster\Core\ViewableInterface;

class View implements ViewableInterface
{
    /**
     * @var \SiteMaster\Core\Auditor\Metric
     */
    protected $metric;

    /**
     * @var \SiteMaster\Core\Auditor\Metric\Mark
     */
    protected $mark;

    public function __construct(array $options)
    {
        if (!isset($options['metrics_id'])) {
            throw new UnexpectedValueException('Please provide a metric id', 404);
        }

        if (!$this->metric = Metric::getByID($options['metrics_id'])) {
            throw new UnexpectedValueException('Unknown metric', 404);
        }

        if (!isset($options['marks_id'])) {
            throw new UnexpectedValueException('Please provide a mark id', 404);
        }

        if (!$this->mark = Metric\Mark::getByID($options['marks_id'])) {
            throw new UnexpectedValueException('Unknown mark', 404);
        }
    }

    public function getURL()
    {
        return Config::get('URL') . 'metrics/' . $this->metric->id . '/marks/' . $this->mark->id . '/';
    }

    public function getPageTitle()
    {
        return 'Mark: ' . $this->mark->name;
    }

    /**
     * @return \SiteMaster\Core\Auditor\Metric\Mark
     */
    public function getMark()
    {
        return $this->mark;
    }

    /**
     * @return \SiteMaster\Core\Auditor\Metric
     */
    public function getMetric()
    {
        return $this->metric;
    }
}