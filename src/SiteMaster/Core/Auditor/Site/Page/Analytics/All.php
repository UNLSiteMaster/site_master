<?php
namespace SiteMaster\Core\Auditor\Site\Page\Analytics;

use DB\RecordList;
use InvalidArgumentException;

class All extends RecordList
{
    public function __construct(array $options = array())
    {
        $this->options = $options + $this->options;

        $options['array'] = self::getBySQL(array(
            'sql'         => $this->getSQL(),
            'returnArray' => true
        ));

        if (!isset($options['data_type'])) {
            throw new InvalidArgumentException('Aa data_type must be set', 500);
        }

        if (!isset($options['data_key'])) {
            throw new InvalidArgumentException('Aa data_key must be set', 500);
        }

        parent::__construct($options);
    }

    public function getDefaultOptions()
    {
        $options = array();
        $options['itemClass'] = '\SiteMaster\Core\Auditor\Site\Page\Analytics';
        $options['listClass'] = __CLASS__;

        return $options;
    }

    public function getSQL()
    {
        //Build the list
        $sql = "
SELECT scanned_page_analytics.id
FROM scanned_page_analytics
JOIN scanned_page ON (scanned_page.id = scanned_page_analytics.scanned_page_id)
JOIN (SELECT MAX(scans.id) as id
        FROM scanned_page
          JOIN scans on (scanned_page.scans_id = scans.id)
        WHERE scans.status = 'COMPLETE'
        GROUP BY scans.sites_id
       ) as completed_scans ON completed_scans.id = scanned_page.scans_id";

        $sql .= " WHERE scanned_page_analytics.data_type = '".self::escapeString($this->options['data_type'])."'
                      AND scanned_page_analytics.data_key = '".self::escapeString($this->options['data_key'])."'";

        $sql .= " ORDER BY scanned_page_analytics.num_instances DESC";
        
        $sql .= $this->getLimit();

        return $sql;
    }

    /**
     * @return \SiteMaster\Core\Auditor\Site\Page\Link
     */
    public function current()
    {
        return parent::current();
    }

    /**
     * Get the limit for the SQL query
     *
     * @return string
     */
    public function getLimit()
    {
        if (!isset($this->options['limit']) || $this->options['limit'] == -1) {
            return '';
        }

        return ' LIMIT ' . (int)$this->options['limit'];
    }
}
