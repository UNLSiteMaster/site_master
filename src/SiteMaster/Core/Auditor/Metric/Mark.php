<?php
namespace SiteMaster\Core\Auditor\Metric;

use DB\Record;
use SiteMaster\Core\Auditor\Metric;
use SiteMaster\Core\Auditor\MetricInterface;
use SiteMaster\Core\Registry\Site\Member;

class Mark extends Record
{
    public $id;                    //int required
    public $metrics_id;            //fk for metrics.id NOT NULL
    public $machine_name;          //VARCHAR(64) NOT NULL, machine readable name
    public $name;                  //TEXT NOT NULL, human readable name
    public $point_deduction;       //DECIMAL(5,2) NOT NULL default=0, points to take off for the mark
    public $description;           //TEXT, a description of the error
    public $help_text;             //TEXT, a how-to-fix text (will be editable by admin)

    public function keys()
    {
        return array('id');
    }

    public static function getTable()
    {
        return 'marks';
    }

    /**
     * Get a mark by a machine name and metric id
     * 
     * @param string $machine_name the machine name of the mark
     * @param int $metric_id the id of the metric
     * @return bool|Mark
     */
    public static function getByMachineNameAndMetricID($machine_name, $metric_id)
    {
        return self::getByAnyField(__CLASS__, 'machine_name', $machine_name, 'metrics_id=' . (int)$metric_id);
    }

    /**
     * Create a new Scan
     *
     * @param int $metric_id the metric id that this mark belongs to
     * @param string $machine_name the machine name of this mark
     * @param string $name the human readable name for this mark
     * @param array $fields an associative array of field names and values
     * @return bool|Mark
     */
    public static function createNewMark($metric_id, $machine_name, $name, array $fields = array())
    {
        $scan = new self();
        $scan->point_deduction = 0;
        $scan->synchronizeWithArray($fields);
        $scan->metrics_id    = $metric_id;
        $scan->machine_name = $machine_name;
        $scan->name         = $name;

        if (!$scan->insert()) {
            return false;
        }
        
        return $scan;
    }

    /**
     * Get the metric for this mark
     * 
     * @return bool|MetricInterface
     */
    public function getMetric()
    {
        $metric_record = Metric::getByID($this->metrics_id);
        
        if (!$metric_plugin = $metric_record->getMetricObject()) {
            return false;
        }
        
        return $metric_plugin;
    }

    /**
     * Get the help file for this mark
     * 
     * @return bool|string
     */
    public function getHelpText()
    {
        if (!$metric_plugin = $this->getMetric()) {
            return false;
        }
        
        $plugin = $metric_plugin->getPlugin();

        $file_path = $plugin->getRootDirectory() . '/help_text/' . $this->machine_name . '.md';

        if (file_exists($file_path)) {
            return file_get_contents($file_path);
        }

        return false;
    }
}
