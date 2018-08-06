<?php
namespace SiteMaster\Core\Auditor;

class SiteMapTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function getURLs()
    {
        $site_map = new SiteMap(__DIR__ . '/../../../data/sample_site_map.xml');
        
        $expected_URLs = array(
            'http://www.example.com/',
            'http://www.example.com/page1',
            'http://www.example.com/page2',
            'http://www.example.com/page3',
            'http://www.example.com/page4',
        );
        
        $this->assertEquals($expected_URLs, $site_map->getURLs());
    }
}
