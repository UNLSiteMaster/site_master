<?php
namespace SiteMaster\Core\Auditor;

use SiteMaster\Core\Config;
use SiteMaster\Core\Util;

class PageTitleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function getPageTitle()
    {
        $logger = new Logger\PageTitle(new Site\Page());
        $parser = new \Spider_Parser();
        $html = file_get_contents(Util::getRootDir() . '/tests/data/example.html');
        $xpath = $parser->parse($html);

        $this->assertEquals('SiteMaster testing site', $logger->getPageTitle($xpath));
    }
}