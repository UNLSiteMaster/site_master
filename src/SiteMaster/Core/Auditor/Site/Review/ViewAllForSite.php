<?php
namespace SiteMaster\Core\Auditor\Site\Review;

use SiteMaster\Core\Auditor\Site\Review;
use SiteMaster\Core\Auditor\Site\Reviews\AllForSite;
use SiteMaster\Core\InvalidArgumentException;
use SiteMaster\Core\Registry\Site;
use Sitemaster\Core\User\Session;
use SiteMaster\Core\ViewableInterface;

class ViewAlLForSite implements ViewableInterface
{
    /**
     * @var array
     */
    public $options = array();

    /**
     * @var Site
     */
    public $site = false;

    /**
     * @var bool|\SiteMaster\Core\User\User
     */
    public $current_user = false;


    function __construct($options = array())
    {
        $this->options += $options;

        //Require login
        Session::requireLogin();

        $this->current_user = Session::getCurrentUser();

        if (!$this->site = Site::getByID($this->options['site_id'])) {
            throw new InvalidArgumentException('Could not find that site', 400);
        }
    }

    /**
     * Only Admin's can view reviews at the moment
     *
     * @return bool
     */
    public function canEdit()
    {
        if (!$this->current_user) {
            return false;
        }

        if ($this->current_user->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * @return AllForSite
     */
    public function getReviews()
    {
        return new AllForSite(array('sites_id'=>$this->site->id));
    }

    public function getURL()
    {
        return $this->site->getURL() . 'reviews/';
    }

    public function getPageTitle()
    {
        return 'Reviews';
    }
}