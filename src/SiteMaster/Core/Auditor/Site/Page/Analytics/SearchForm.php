<?php
namespace SiteMaster\Core\Auditor\Site\Page\Analytics;

use SiteMaster\Core\Config;
use SiteMaster\Core\ViewableInterface;

class SearchForm implements ViewableInterface
{
    protected $results;
    
    public $options;
    
    function __construct($options = [])
    {
        if (isset($options['data_type'], $options['data_key'])) {
            //Try to get the data
            $query_options = [
                'data_type' => $options['data_type'],
                'data_key' => $options['data_key'],
            ];
            
            $this->results = new All($query_options);
            var_dump($this->results);
        }
        
        $this->options = $options;
    }

    public function getURL()
    {
        return Config::get('URL') . 'analytics/';
    }

    public function getPageTitle()
    {
        return 'Analytics Search';
    }
    
    public function getResults()
    {
        return $this->results;
    }
}