<?php
namespace SiteMaster\Core\Group;

use SiteMaster\Core\Auditor\Group\History\GroupHistoryList\ForGroup;
use SiteMaster\Core\Config;
use SiteMaster\Core\Events\Navigation\GroupCompile;
use SiteMaster\Core\InvalidArgumentException;
use SiteMaster\Core\Plugin\PluginManager;
use SiteMaster\Core\Registry\GroupHelper;
use SiteMaster\Core\Registry\Sites\WithGroup;
use SiteMaster\Core\UnexpectedValueException;
use SiteMaster\Core\ViewableInterface;

class View implements ViewableInterface
{
    /**
     * @var array
     */
    public $options = array();
    
    protected $group_name;

    /**
     * @param array $options
     * @throws \SiteMaster\Core\UnexpectedValueException
     * @throws \SiteMaster\Core\InvalidArgumentException
     */
    function __construct($options = array())
    {
        $this->options += $options;

        if (!isset($this->options['group_name'])) {
            throw new InvalidArgumentException('A group must be provided', 400);
        }

        $helper = new GroupHelper();
        
        if (!$helper->groupExists($this->options['group_name'])) {
            throw new UnexpectedValueException('That user could not be found', 400);
        }
        
        $this->group_name = $options['group_name'];
    }
    

    /**
     * Get the url for this page
     *
     * @return bool|string
     */
    public function getURL()
    {
        if (!$this->group_name) {
            return false;
        }

        return Config::get('URL') . 'groups/'.$this->group_name;
    }

    /**
     * Get the title for this page
     *
     * @return string
     */
    public function getPageTitle()
    {
        if (!$this->group_name) {
            return "Group: (unknown)";
        }

        return 'Group: ' . $this->group_name;
    }

    /**
     * @param $options
     * @return ForGroup
     */
    public function getHistory($options)
    {
        $options = $options + ['group_name'=>$this->group_name];
        
        return new ForGroup($options);
    }

    /**
     * @return mixed
     */
    public function getGroupNavigation()
    {
        $nav = PluginManager::getManager()->dispatchEvent(
            GroupCompile::EVENT_NAME,
            new GroupCompile($this->group_name)
        );
        
        return $nav->getNavigation();
    }

    /**
     * @return mixed
     */
    public function getGroupName()
    {
        return $this->group_name;
    }

    /**
     * @return WithGroup
     */
    public function getSites()
    {
        return new WithGroup(['group_name'=>$this->group_name, 'limit'=>90]);
    }
}
