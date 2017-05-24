<?php
namespace SiteMaster\Core\Auditor\Site\Overrides;

use DB\RecordList;
use InvalidArgumentException;

class AllForSite extends RecordList
{
    public function __construct(array $options = array())
    {
        $this->options = $options + $this->options;

        if (!isset($options['sites_id'])) {
            throw new InvalidArgumentException('An sites_id must be set', 500);
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
        $options['itemClass'] = '\SiteMaster\Core\Auditor\Override';
        $options['listClass'] = __CLASS__;

        return $options;
    }

    public function getWhere()
    {
        return 'WHERE sites_id = ' . (int)$this->options['sites_id'];
    }

    public function getSQL()
    {
        //Build the list
        $sql = "SELECT overrides.id
                FROM overrides"
                . " " . $this->getWhere()
                . " " . $this->getLimit();

        return $sql;
    }

    /**
     * @return \SiteMaster\Core\Auditor\Override
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
