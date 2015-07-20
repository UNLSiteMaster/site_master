<?php
namespace SiteMaster\Core\Auditor\Filter;

class FileExtension extends \Spider_UriFilterInterface
{
    function accept()
    {
        //First parse with parse_url to get the correct file name
        $url_parts = parse_url($this->current());
        
        if (!isset($url_parts['path'])) {
            //No extension given, this is okay...
            return true;
        }
        
        $path_parts = pathinfo($url_parts['path']);
        
        if (!isset($path_parts['extension'])
            || stripos($path_parts['extension'], 'htm') === 0
            || stripos($path_parts['extension'], 'html') === 0
            || stripos($path_parts['extension'], 'php') === 0
            || stripos($path_parts['extension'], 'shtml') === 0
            || stripos($path_parts['extension'], 'asp') === 0
            || stripos($path_parts['extension'], 'aspx') === 0
            || stripos($path_parts['extension'], 'jsp') === 0
            || stripos($path_parts['extension'], 'htm') === 0) {
            return true;
        }

        return false;
    }
}