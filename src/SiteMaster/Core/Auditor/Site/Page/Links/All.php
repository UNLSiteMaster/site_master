<?php
namespace SiteMaster\Core\Auditor\Site\Page\Links;

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
        $options['itemClass'] = '\SiteMaster\Core\Auditor\Site\Page\Link';
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
        $sql = "SELECT scanned_page_links.id
                FROM scanned_page_links
                " . $this->getWhere() . "
                ORDER BY scanned_page_links.date_created DESC";

        return $sql;
    }
}
