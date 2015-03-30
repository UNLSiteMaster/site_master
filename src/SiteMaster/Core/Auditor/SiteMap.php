<?php
namespace SiteMaster\Core\Auditor;

use SiteMaster\Core\Registry\Site\Member;

class SiteMap
{
    public $site_map_url;

    public function __construct($site_map_url)
    {
        $this->site_map_url = $site_map_url;
    }
    
    public function getURLs()
    {
        if (empty($this->site_map_url)) {
            return false;
        }
        
        if (!$xml = @file_get_contents($this->site_map_url)) {
            return false;
        }

        try {
            $xml = new \SimpleXMLElement($xml);
        } catch (\Exception $e) {
            return false;
        }

        $nodes = $xml->xpath('//*[local-name()="loc"]');

        $urls = array();
        foreach ($nodes as $node) {
            $urls[] = (string)$node;
        }
        
        return array_unique($urls);
    }
}
