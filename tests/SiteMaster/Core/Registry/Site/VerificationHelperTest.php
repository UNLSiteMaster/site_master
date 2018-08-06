<?php
namespace SiteMaster\Core\Registry\Site;

class VerificationHelperTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @test
     */
    public function testVerifyByMetaTag()
    {
        $html = file_get_contents(__DIR__.'/../../../../data/verification_example.html');

        $helper = new VerificationHelper();
        $result = $helper->verifyByMetaTag($html);
        
        $expected = ['code-1', 'code-2'];
        
        $this->assertEquals($expected, $result, 'the correct meta content should be found');
    }
}
