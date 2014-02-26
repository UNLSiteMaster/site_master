<?php
namespace SiteMaster\Core;

class ThemeHelper {
    
    public function trimBaseURL($base_url, $url)
    {
        return str_replace(strtolower($base_url), '', strtolower($url));
    }
}