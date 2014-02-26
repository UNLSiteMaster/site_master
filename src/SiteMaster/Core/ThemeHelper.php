<?php
namespace SiteMaster\Core;

class ThemeHelper {

    /**
     * Trim the base url off of a given url
     * 
     * @param string $base_url
     * @param string $url
     * @return mixed
     */
    public function trimBaseURL($base_url, $url)
    {
        return str_replace(strtolower($base_url), '', strtolower($url));
    }
}