<?php
namespace SiteMaster\Core\Auditor;

use SiteMaster\Core\Auditor\Parser\HTML5;
use SiteMaster\Core\Config;
use SiteMaster\Core\Util;

class PageTitleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function getPageTitle()
    {
        $logger = new Logger\PageTitle(new Site\Page());
        $parser = new HTML5();
        $html = file_get_contents(Util::getRootDir() . '/tests/data/example.html');
        $xpath = $parser->parse($html);

        $this->assertEquals('SiteMaster testing site', $logger->getPageTitle($xpath));
    }
}