<?php
namespace SiteMaster\Core\Auditor\Site\History\MetricHistoryList;

use DB\RecordList;

class All extends RecordList
{
    public function __construct(array $options = array())
    {
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
        $options['itemClass'] = '\SiteMaster\Core\Auditor\Site\History\MetricHistory';
        $options['listClass'] = __CLASS__;

        return $options;
    }

    public function getWhere()
    {
        return '';
    }

    public function getSQL()
    {
        //Build the list
        $sql = "SELECT site_scan_metric_history.id
                FROM site_scan_metric_history
                LEFT JOIN site_scan_history ON (site_scan_history.id = site_scan_metric_history.site_scan_history_id)
                " . $this->getWhere() . "
                ORDER BY site_scan_history.date_created DESC
                " . $this->getLimit();

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

        return 'LIMIT ' . (int)$this->options['limit'];
    }
}
