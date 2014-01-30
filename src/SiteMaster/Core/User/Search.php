<?php
namespace SiteMaster\Core\User;

use DB\RecordList;
use SiteMaster\Core\InvalidArgumentException;

class Search extends RecordList
{
    public function __construct(array $options = array())
    {
        if (!isset($options['term'])) {
            throw new InvalidArgumentException('term was not provided', 500);
        }
        
        $options['array'] = self::getBySQL(array(
            'sql'         => $this->getSQL($options['term']),
            'returnArray' => true
        ));

        parent::__construct($options);
    }

    public function getDefaultOptions()
    {
        $options = array();
        $options['itemClass'] = '\SiteMaster\Core\User\User';
        $options['listClass'] = __CLASS__;

        return $options;
    }

    public function getSQL($term)
    {
        $term = self::escapeString($term);
        //Build the list
        $sql = "SELECT users.id
                FROM users
                WHERE users.uid = '" . $term . "'
                    OR users.email = '" . $term . "'
                    OR users.first_name LIKE '" . $term . "'
                    OR users.last_name LIKE '" . $term . "'
                    OR concat(users.first_name, ' ', users.last_name) LIKE '" . $term . "'
                ORDER BY users.last_name ASC";

        return $sql;
    }
}
