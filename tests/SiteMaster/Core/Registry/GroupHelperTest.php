<?php
namespace SiteMaster\Core\Registry;

use SiteMaster\Core\Config;

class GroupHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function getPrimaryGroup()
    {
        $helper = new GroupHelper();

        $this->assertEquals('group_1', $helper->getPrimaryGroup('http://www.test.com/blah/blah'), 'should match group_1');
        $this->assertEquals('group_2', $helper->getPrimaryGroup('http://unlsitemaster.github.io/test_site/'), 'should match group_2');
        $this->assertEquals(GroupHelper::DEFAULT_GROUP_NAME, $helper->getPrimaryGroup('http://example.org/'), 'should match the default group');
    }

    /**
     * @test
     */
    public function getConfigForGroup()
    {
        $helper = new GroupHelper();

        $group_1_config = $helper->getConfigForGroup('group_1');
        $group_2_config = $helper->getConfigForGroup('group_2');
        $default = $helper->getConfigForGroup(GroupHelper::DEFAULT_GROUP_NAME);
        
        $this->assertEquals(true, $group_1_config['METRICS']['example']['test'], 'metric config should be passed');
        $this->assertEquals(false, $group_2_config['METRICS']['example']['test'], 'metric config should be passed');
        $this->assertTrue(!empty($default), 'the default config should not be empty');
        $this->assertEquals(Config::get('SITE_PASS_FAIL'), $default['SITE_PASS_FAIL'], 'should match the default config');
    }
}
