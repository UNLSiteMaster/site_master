<?php
namespace SiteMaster\Core\Registry;

use SiteMaster\Core\Util;

class UtilTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function validateBaseURL()
    {
        $this->assertEquals('http://test/', Util::validateBaseURL('http://test/'), 'valid base url');
        $this->assertEquals('http://test/', Util::validateBaseURL('http://test/?#'), 'valid base url');
        $this->assertEquals('https://www.test.com/', Util::validateBaseURL('https://www.test.com/'), 'valid base url');
        $this->assertEquals('https://www.test.com:80/', Util::validateBaseURL('https://www.test.com:80/'), 'valid base url');
    }

    /**
     * @expectedException        \SiteMaster\Core\InvalidArgumentException
     * @expectedExceptionMessage Invalid scheme
     * 
     * @test
     */
    public function testValidateBaseURLInvalidScheme()
    {
        Util::validateBaseURL('file://test.com/');
    }

    /**
     * @expectedException        \SiteMaster\Core\InvalidArgumentException
     * @expectedExceptionMessage Invalid host
     *
     * @test
     */
    public function testValidateBaseURLInvalidHost()
    {
        Util::validateBaseURL('test');
    }

    /**
     * @expectedException        \SiteMaster\Core\PathRequiredException
     * @expectedExceptionMessage A path must be set
     *
     * @test
     */
    public function testValidateBaseURLMissingPath()
    {
        Util::validateBaseURL('http://www.test.com');
    }

    /**
     * @expectedException        \SiteMaster\Core\PathRequiredException
     * @expectedExceptionMessage The Path must end in a /
     *
     * @test
     */
    public function testValidateBaseURLMissingInvalidPath()
    {
        Util::validateBaseURL('http://www.test.com/test');
    }

    /**
     * @expectedException        \SiteMaster\Core\InvalidArgumentException
     * @expectedExceptionMessage A query string must not be set
     *
     * @test
     */
    public function testValidateBaseURLMissingInvalidQuery()
    {
        Util::validateBaseURL('http://www.test.com/test/?test');
    }

    /**
     * @expectedException        \SiteMaster\Core\InvalidArgumentException
     * @expectedExceptionMessage A fragment must not be set
     *
     * @test
     */
    public function testValidateBaseURLMissingInvalidFragment()
    {
        Util::validateBaseURL('http://www.test.com/test/#fragment');
    }

    /**
     * @expectedException        \SiteMaster\Core\InvalidArgumentException
     * @expectedExceptionMessage A user must not be set
     *
     * @test
     */
    public function testValidateBaseURLMissingInvalidUser()
    {
        Util::validateBaseURL('http://user@www.test.com/test/');
    }

    /**
     * @expectedException        \SiteMaster\Core\InvalidArgumentException
     *
     * @test
     */
    public function testValidateBaseURLMissingInvalidPass()
    {
        Util::validateBaseURL('http://:pass@www.test.com/test/');
    }

    /**
     * @test
     */
    public function stripURIFragment()
    {
        $this->assertEquals('http://www.test.com/', Util::stripURIFragment('http://www.test.com/#'));
        $this->assertEquals('http://www.test.com/', Util::stripURIFragment('http://www.test.com/'));
        $this->assertEquals('http://www.test.com/?test=test', Util::stripURIFragment('http://www.test.com/?test=test#test'));
    }
}
