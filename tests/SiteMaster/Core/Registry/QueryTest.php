<?php
namespace SiteMaster\Core\Registry;

class QueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function getQueryType()
    {
        $query = new Query();
        
        $this->assertEquals(Query::QUERY_TYPE_URL, $query->getQueryType('http://www.domain.com/'));
        $this->assertEquals(Query::QUERY_TYPE_URL, $query->getQueryType('http://www.domain.com/test/'));
        $this->assertEquals(Query::QUERY_TYPE_URL, $query->getQueryType('http://www.domain.com/test/test.php?query#fragment'));
        $this->assertEquals(Query::QUERY_TYPE_URL, $query->getQueryType('http://www.domain.com'));

        $this->assertEquals(Query::QUERY_TYPE_USER, $query->getQueryType('uid@provider'));
        $this->assertEquals(Query::QUERY_TYPE_USER, $query->getQueryType('test'), 'should fall back to a user');

        $this->assertEquals(Query::QUERY_TYPE_ALL, $query->getQueryType('*'));
    }
}
