<?php
namespace SiteMaster\Core\Auditor\Site\Page\PageHasFeatureAnalytics;

use DB\RecordList;
use InvalidArgumentException;

class All extends RecordList
{
    public function __construct(array $options = array())
    {;
        if (!isset($options['feature_ids'])) {
            throw new InvalidArgumentException('Aa feature_ids must be set', 500);
        }

        //Sanitize for query
        $options['feature_ids'] = array_map('intval', $options['feature_ids']);
        
        $this->options = $options + $this->options;

        $options['array'] = self::getBySQL(array(
            'sql'         => $this->getSQL(),
            'returnArray' => true
        ));

        parent::__construct($options);
    }

    public function getDefaultOptions()
    {
        $options = array();
        $options['itemClass'] = '\SiteMaster\Core\Auditor\Site\Page\PageHasFeatureAnalytics';
        $options['listClass'] = __CLASS__;

        return $options;
    }

    public function getSQL()
    {
        //Build the list
        $sql = "
SELECT scanned_page_has_feature_analytics.id
FROM scanned_page_has_feature_analytics
JOIN scanned_page ON (scanned_page.id = scanned_page_has_feature_analytics.scanned_page_id)
JOIN feature_analytics ON (feature_analytics.id IN (".implode(',',$this->options['feature_ids']).") AND scanned_page_has_feature_analytics.feature_analytics_id = feature_analytics.id)
JOIN (SELECT MAX(scans.id) as id
        FROM scanned_page
          JOIN scans on (scanned_page.scans_id = scans.id)
        WHERE scans.status = 'COMPLETE'
        GROUP BY scans.sites_id
       ) as completed_scans ON completed_scans.id = scanned_page.scans_id";

        $sql .= " ORDER BY scanned_page_has_feature_analytics.num_instances DESC";
        
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
        if (isset($this->options['limit_offset'], $this->options['limit_rows'])) {
            return ' LIMIT ' . (int)$this->options['limit_offset'] . ', ' . (int)$this->options['limit_rows'];
        }

        return '';
    }
}
