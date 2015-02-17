<?php
namespace SiteMaster\Core\Admin;

use SiteMaster\Core\Auditor\Site\Review;
use SiteMaster\Core\Auditor\Site\Reviews\AllForSite;
use SiteMaster\Core\Auditor\Site\Reviews\Unfinished;
use SiteMaster\Core\Config;
use SiteMaster\Core\InvalidArgumentException;
use SiteMaster\Core\Registry\Site;
use Sitemaster\Core\User\Session;
use SiteMaster\Core\ViewableInterface;

class Reviews implements ViewableInterface
{
    /**
     * @var array
     */
    public $options = array();

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

        if (!$this->canView()) {
            throw new InvalidArgumentException('You do not have permission to view this', 4003);
        }
    }

    /**
     * Only Admin's can view reviews at the moment
     *
     * @return bool
     */
    public function canView()
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
        return new Unfinished();
    }

    public function getURL()
    {
        return Config::get('URL') . 'admin/reviews/';
    }

    public function getPageTitle()
    {
        return 'Unfinished Manual Reviews';
    }
}