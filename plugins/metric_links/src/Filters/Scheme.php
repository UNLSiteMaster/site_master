<?php
namespace SiteMaster\Plugins\Metric_links\Filters;

class Scheme extends \Spider_UriFilterInterface
{
    function accept()
    {
        //$this->current() contains the base uri, so the 'protocol' it probably won't start with the following.
        if (stripos($this->current(), 'http:') === 0
            || stripos($this->current(), 'https:') === 0) {
            return true;
        }

        return false;
    }
}
