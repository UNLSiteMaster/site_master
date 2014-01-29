<?php
namespace SiteMaster\Core\Registry\Site;

use SiteMaster\Core\Controller;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\RuntimeException;
use SiteMaster\Core\UnexpectedValueException;
use Sitemaster\Core\User\Session;
use SiteMaster\Core\Util;
use SiteMaster\Core\ViewableInterface;
use SiteMaster\Core\PostHandlerInterface;

class VerifyForm implements ViewableInterface, PostHandlerInterface
{
    /**
     * @var array
     */
    public $options = array();

    /**
     * @var \SiteMaster\Core\Registry\Site
     */
    public $site = false;

    /**
     * @var bool|\SiteMaster\Core\User\
     */
    public $user = false;

    /**
     * @var bool|Member
     */
    public $membership = false;


    function __construct($options = array())
    {
        $this->options += $options;

        //Require login
        Session::requireLogin();

        $this->user = Session::getCurrentUser();

        //get the site
        if (!isset($this->options['site_id'])) {
            throw new \InvalidArgumentException('a site id is required', 400);
        }

        if (!$this->site = Site::getByID($this->options['site_id'])) {
            throw new \InvalidArgumentException('Could not find that site', 400);
        }

        if (!$this->membership = Member::getByUserIDAndSiteID($this->user->id, $this->site->id)) {
            throw new \InvalidArgumentException('Could not find a membership to verify', 400);
        }
        
        if ($this->membership->isVerified()) {
            throw new \InvalidArgumentException('That membership is already verified', 400);
        }
    }

    /**
     * Get the url for this page
     *
     * @return bool|string
     */
    public function getURL()
    {
        return $this->site->getURL() . 'verify/';
    }

    /**
     * Get the title for this page
     *
     * @return string
     */
    public function getPageTitle()
    {
        return 'Verify Membership for ' . $this->user->getName() . ' at ' . $this->site->base_url;
    }

    public function handlePost($get, $post, $files)
    {
        if (!isset($post['type'])) {
            throw new \InvalidArgumentException('a verification type must be provided', 400);
        }
        
        switch ($post['type']) {
            case 'Manually Verify Now':
                $this->manuallyVerify();
                break;
            default:
                throw new UnexpectedValueException('That type is not supported', 400);
        }
    }
    
    protected function manuallyVerify()
    {
        $result = Util::getHTTPInfo($this->getVerificationURL());
        if (!$result['okay']) {
            throw new RuntimeException('Unable to find the verification file.  Please make sure it is present and try again.', 400);
        }
        
        $this->membership->verify();
        
        Controller::redirect($this->site->getURL() . 'members/');
    }

    public function getEditURL()
    {
        return $this->getURL();
    }

    public function getVerificationURL()
    {
        return $this->site->base_url . 'sitemaster_v_' . $this->membership->verification_code . '.txt';
    }
}
