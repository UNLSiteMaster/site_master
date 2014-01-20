<?php
namespace SiteMaster\Core\Registry;

use SiteMaster\Core\DBTests\BaseTestDataInstaller;
use SiteMaster\Core\DBTests\DBTestCase;

class QueryDBTest extends DBTestCase
{
    /**
     * @test
     */
    public function getByUser()
    {
        $this->setUpDB();
        
        $query = new Query();
        $result = $query->query('test?2');
        $sites = array();
        
        foreach ($result as $site) {
            $sites[] = $site->base_url;
        }
        
        $expected = array(
            'http://www.test.com/',
            'http://www.test.com/test/',
        );
        
        $this->assertEquals($expected, $sites, 'This user should have two accepted sites');

        $result = $query->query('test?1');
        $sites = array();

        foreach ($result as $site) {
            $sites[] = $site->base_url;
        }

        $expected = array(
            'http://www.test.com/'
        );

        $this->assertEquals($expected, $sites, 'This user should have 1 accepted site (and 1 pending)');
    }

    /**
     * @test
     */
    public function getByURL()
    {
        $this->setUpDB();

        $query = new Query();
        $result = $query->query('http://www.test.com/test/');
        $sites = array();

        foreach ($result as $site) {
            $sites[] = $site->base_url;
        }

        $expected = array(
            'http://www.test.com/test/',
            'http://www.test.com/',
        );

        $this->assertEquals($expected, $sites, 'This should return two sites, with the closest site first');
    }

    /**
     * @test
     */
    public function getByAll()
    {
        $this->setUpDB();

        $query = new Query();
        $result = $query->query('*');
        $sites = array();

        foreach ($result as $site) {
            $sites[] = $site->base_url;
        }

        $expected = array(
            'http://www.test.com/',
            'http://www.test.com/test/',
        );

        $this->assertEquals($expected, $sites, 'This should return all (2) sites, in ascending order');
    }
    
    public function setUpDB()
    {
        $this->cleanDB();
        $this->installBaseDB();
        $this->installMockData(new BaseTestDataInstaller());
    }
}
