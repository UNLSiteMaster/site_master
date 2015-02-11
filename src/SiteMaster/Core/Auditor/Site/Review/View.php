<?php
namespace SiteMaster\Core\Auditor\Site\Review;

use SiteMaster\Core\AccessDeniedException;
use SiteMaster\Core\Auditor\Site\Review;
use SiteMaster\Core\InvalidArgumentException;
use SiteMaster\Core\Registry\Site;
use Sitemaster\Core\User\Session;
use SiteMaster\Core\ViewableInterface;

class View implements ViewableInterface
{
    /**
     * @var array
     */
    public $options = array();

    /**
     * @var \SiteMaster\Core\Auditor\Site\Review
     */
    public $review = false;

    /**
     * @var \SiteMaster\Core\Registry\Site
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

        //get the review
        if (isset($this->options['reviews_id'])) {
            if (!$this->review = Review::getByID($this->options['reviews_id'])) {
                throw new InvalidArgumentException('Could not find that review', 400);
            }

            $this->site = $this->review->getSite();
        } else {
            if (!$this->site = Site::getByID($this->options['site_id'])) {
                throw new InvalidArgumentException('Could not find that site', 400);
            }
        }

        $this->current_user = Session::getCurrentUser();

        if (!$this->canView()) {
            throw new AccessDeniedException('You do not have permission to view this review.  You must be a member of the site.', 403);
        }
    }

    /**
     * Get the url for this page
     *
     * @return bool|string
     */
    public function getURL()
    {
        return $this->review->getURL();
    }

    /**
     * Get the title for this page
     *
     * @return string
     */
    public function getPageTitle()
    {
        if (!empty($this->review->date_reviewed)) {
            return 'Manual Review conducted on ' . date('F j, Y', strtotime($this->review->date_reviewed));
        }
        
        return 'Manual Review';
    }

    /**
     * Only Admin's or members of a site can edit reviews
     *
     * @return bool
     */
    public function canView()
    {
        if (!$this->current_user) {
            return false;
        }

        if ($this->site->userIsVerified($this->current_user)) {
            return true;
        }
        
        if ($this->current_user->isAdmin()) {
            return true;
        }

        return false;
    }
}
