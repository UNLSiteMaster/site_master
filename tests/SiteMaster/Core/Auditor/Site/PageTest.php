<?php
namespace SiteMaster\Core\Auditor\Site;

class PageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function getSanitizedURI()
    {
        $page = new Page();
        
        
        $page->uri = 'http://example.org/test?test test test';
        $this->assertEquals('http://example.org/test?test%20test%20test', $page->getSanitizedURI());

        $page->uri = 'http://example.org/test';
        $this->assertEquals('http://example.org/test', $page->getSanitizedURI());
    }
}
