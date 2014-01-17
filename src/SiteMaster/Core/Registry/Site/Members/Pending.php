<?php
namespace SiteMaster\Core\Registry\Site\Members;

use DB\RecordList;
use SiteMaster\Core\InvalidArgumentException;

class Pending extends RecordList
{
    public function __construct(array $options = array())
    {
        if (!isset($options['site_id'])) {
            throw new InvalidArgumentException('A site_id must be set', 500);
        }

        $options['array'] = self::getBySQL(array(
            'sql'         => $this->getSQL($options['site_id']),
            'returnArray' => true
        ));

        parent::__construct($options);
    }

    public function getDefaultOptions()
    {
        $options = array();
        $options['itemClass'] = '\SiteMaster\Registry\Site\Member';
        $options['listClass'] = __CLASS__;

        return $options;
    }

    public function getSQL($site_id)
    {
        //Build the list
        $sql = "SELECT id
                FROM site_members
                WHERE sites_id = " .  (int)$site_id . "
                AND status = 'PENDING'
                ORDER by id ASC";

        return $sql;
    }
}
