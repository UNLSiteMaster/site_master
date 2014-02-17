<?php
namespace SiteMaster\Plugins\Metric_links\Filters;

class Scheme extends \Spider_UriFilterInterface
{
    function accept()
    {
        //$this->current() contains the base uri, so the 'protocol' it probably won't start with the following.
        if (stripos($this->current(), 'javascript:') !== false
            || stripos($this->current(), 'tel:') !== false
            || stripos($this->current(), 'mailto:') !== false) {
            return false;
        }

        return true;
    }
}