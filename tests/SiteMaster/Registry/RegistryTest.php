<?php
namespace SiteMaster\Registry;

class RegistryTest extends \PHPUnit_Framework_TestCase
{
    public function testGetPossibleSites()
    {
        $registry = new Registry();

        $this->assertEquals(
            array('http://www.domain.com/path1/path2/path3/',
                  'http://www.domain.com/path1/path2/',
                  'http://www.domain.com/path1/',
                  'http://www.domain.com/'),
            $registry->getPossibleSiteURIs('http://www.domain.com/path1/path2/path3/index.php?test=false#1')
        );
    }
}