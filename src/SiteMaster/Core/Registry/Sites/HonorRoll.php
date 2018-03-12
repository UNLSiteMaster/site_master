<?php
namespace SiteMaster\Core\Registry\Sites;

use DB\RecordList;
use SiteMaster\Core\Registry\Site;

class HonorRoll extends RecordList
{
    public function __construct(array $options = array())
    {
        $this->options = $options + $this->options;

        if (!isset($this->options['group_name'])) {
            //Default to queueing production sites
            throw new \Exception('group_name is required');
        }
        
        if (!isset($this->options['honor_type'])) {
            //Default to queueing production sites
            $this->options['honor_type'] = 100;
        }

        $options['array'] = self::getBySQL(array(
            'sql'         => $this->getSQL(),
            'returnArray' => true
        ));

        parent::__construct($options);
    }

    public function getDefaultOptions()
    {
        $options = array();
        $options['itemClass'] = '\SiteMaster\Core\Registry\Site';
        $options['listClass'] = __CLASS__;

        return $options;
    }

    public function getWhere()
    {
        $where = 'WHERE sites.production_status = "PRODUCTION" AND group_name = "' . self::escapeString($this->options['group_name']) . '"';
        
        if ($this->options['honor_type'] === 100) {
            return $where .= ' AND scans.gpa = 100';
        }
        
        //Otherwise the score is in the 90s
        return $where .= ' AND (scans.gpa >= 90 AND scans.gpa < 100)';
    }

    public function getSQL()
    {
        //Build the list
        $sql = "SELECT sites.id as id
                FROM sites
                LEFT JOIN (
                    SELECT max(scans.id) as id, scans.sites_id as sites_id
                    FROM scans
                    WHERE scans.status = 'COMPLETE'
                    AND scans.end_time >= (NOW() - INTERVAL 2 MONTH)
                    GROUP BY scans.sites_id
                ) as max_scans ON (max_scans.sites_id = sites.id)
                LEFT JOIN scans ON (scans.id = max_scans.id)
                " . $this->getWhere() . "
                ORDER BY scans.gpa DESC, sites.base_url ASC";

        return $sql;
    }
}
