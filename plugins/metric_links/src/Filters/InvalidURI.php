<?php
namespace SiteMaster\Plugins\Metric_links\Filters;

class InvalidURI extends \Spider_UriFilterInterface
{
    function accept()
    {
        if (filter_var($this->current(), FILTER_VALIDATE_URL) == false) {
            return false;
        }

        return true;
    }
}
