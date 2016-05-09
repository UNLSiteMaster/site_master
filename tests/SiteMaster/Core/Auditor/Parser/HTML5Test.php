<?php
namespace SiteMaster\Core\Auditor\Parser;

use SiteMaster\Core\Util;

class HTML5Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testHtml5EncodedLinks()
    {
        $parser = new HTML5();
        $html = file_get_contents(Util::getRootDir() . '/tests/data/html5links.html');
        $xpath = $parser->parse($html);
        
        $nodes = $xpath->query(
            "//xhtml:a[@href]/@href | //a[@href]/@href"
        );
        
        $urls = [];
        foreach ($nodes as $node) {
            $urls[] = $node->nodeValue;
        }
        
        $expected = [
            'https://bulletin.unl.edu/undergraduate/',
            'https://bulletin.unl.edu/undergraduate/',
            'https://bulletin.unl.edu/undergraduate/?u=http%3A%2F%2Fexample.com%2Ftest%0D%0A',
            'https://bulletin.unl.edu/undergraduate/?u=http%3A%2F%2Fexample.com%2Ftest%0D%0A',
        ];

        $this->assertEquals($expected, $urls, 'URLs should be decoded correctly');
    }
}