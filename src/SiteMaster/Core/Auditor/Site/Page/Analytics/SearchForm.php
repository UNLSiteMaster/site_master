<?php
namespace SiteMaster\Core\Auditor\Site\Page\Analytics;

use SiteMaster\Core\Config;
use SiteMaster\Core\ViewableInterface;

class SearchForm implements ViewableInterface
{
    protected $results;
    
    protected $total = 0;
    
    public $options;
    
    public $rows_per_page = 100;
    public $pages_remain = false;
    
    function __construct($options = [])
    {
        if (isset($options['data_type'], $options['data_key'], $options['data_specificity'])) {
            //Try to get the data
            $query_options = [
                'data_type' => $options['data_type'],
                'data_key' => strtolower($options['data_key']),
                'data_specificity' => strtolower($options['data_specificity']),
                'limit_offset' => 0,
            ];
            
            //Get all the total of all records
            $all_records = new All($query_options);
            $this->total = $all_records->count();
            unset($all_records);
            
            if (isset($options['page'])) {
                if ($options['page'] == 1) {
                    $query_options['limit_offset'] = 0;
                } else {
                    $query_options['limit_offset'] = $options['page'] * $this->rows_per_page - $this->rows_per_page;
                }
            }
            
            $query_options['limit_rows'] = $this->rows_per_page;

            $this->results = new All($query_options);
            
            if (($query_options['limit_offset'] + $this->rows_per_page) <= $this->total) {
                $this->pages_remain = true;
            }
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
    
    public function getTotal()
    {
        return $this->total;
    }
    
    public function getNextPageURL()
    {
        if (!$this->pages_remain) {
            return false;
        }
        
        $url = $this->getURL() . '?data_type='.$this->options['data_type'] . '&data_key='.$this->options['data_key'];
        if (!isset($this->options['page'])) {
            return $url . '&page=2';
        }

        return $url . '&page='.($this->options['page']+1);
    }

    public function getPreviousPageURL()
    {
        if (!isset($this->options['page']) || $this->options['page'] == 1) {
            return false;
        }

        $url = $this->getURL() . '?data_type='.$this->options['data_type'] . '&data_key='.$this->options['data_key'];

        return $url . '&page='.($this->options['page']-1);
    }
}