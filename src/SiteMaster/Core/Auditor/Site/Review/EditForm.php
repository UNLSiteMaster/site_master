<?php
namespace SiteMaster\Core\Auditor\Site\Review;

use SiteMaster\Core\AccessDeniedException;
use SiteMaster\Core\Auditor\Site\Review;
use SiteMaster\Core\Config;
use SiteMaster\Core\Controller;
use SiteMaster\Core\FlashBagMessage;
use SiteMaster\Core\InvalidArgumentException;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\Registry\Sites\All;
use SiteMaster\Core\RuntimeException;
use SiteMaster\Core\UnexpectedValueException;
use Sitemaster\Core\User\Session;
use SiteMaster\Core\Util;
use SiteMaster\Core\ViewableInterface;
use SiteMaster\Core\PostHandlerInterface;

class EditForm implements ViewableInterface, PostHandlerInterface
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

        if (!$this->canEdit()) {
            throw new AccessDeniedException('You do not have permission to edit this review.  You must be an admin.', 403);
        }
    }

    /**
     * Get the url for this page
     *
     * @return bool|string
     */
    public function getURL()
    {
        return $this->review->getURL() . 'edit/';
    }

    /**
     * Get the title for this page
     *
     * @return string
     */
    public function getPageTitle()
    {
        return 'Edit Review';
    }

    /**
     * Only Admin's can edit reviews
     *
     * @return bool
     */
    public function canEdit()
    {
        if (!$this->current_user) {
            return false;
        }

        $review = $this->review;
        
        if (false == $review) {
            $review = new Review();
        }
        
        return $review->canEdit($this->current_user);
    }

    public function handlePost($get, $post, $files)
    {
        if (!isset($post['action'])) {
            throw new InvalidArgumentException('An action must be specified', 400);
        }

        switch ($post['action']) {
            case 'edit':
                $this->edit($get, $post, $files);
                break;
            case 'delete':
                $this->delete($get, $post, $files);
                break;
            default:
                throw new InvalidArgumentException('An invalid action was given', 400);
        }
    }

    /**
     * handle the edit post action
     *
     * @param $get
     * @param $post
     * @param $files
     */
    protected function edit($get, $post, $files)
    {
        if (!isset($post['date_scheduled'])) {
            throw new InvalidArgumentException('The review must be scheduled.', 400);
        }
        
        if (!$this->review) {
            $this->review = Review::createNewReview($this->site->id, $this->current_user->id, $post['date_scheduled']);
        }
        
        $this->review->date_scheduled = $post['date_scheduled'];

        if (isset($post['date_reviewed'])) {
            $this->review->date_reviewed = Util::epochToDateTime(strtotime($post['date_reviewed']));
        }

        if (isset($post['internal_notes'])) {
            $this->review->internal_notes = $post['internal_notes'];
        }

        if (isset($post['public_notes'])) {
            $this->review->public_notes = $post['public_notes'];
        }

        if (isset($post['status'])) {
            $valid_statuses = array(
                Review::STATUS_REVIEW_FINISHED,
                Review::STATUS_SCHEDULED,
                REview::STATUS_IN_REVIEW,
            );
            
            if (!in_array($post['status'], $valid_statuses)) {
                throw new InvalidArgumentException('A valid status was not provided.', 400);
            }
            
            $this->review->status = $post['status'];
        }
        
        if (isset($post['result'])) {
            $valid_results = array(
                Review::RESULT_NEEDS_WORK,
                Review::RESULT_OKAY,
                '',
            );

            if (!in_array($post['result'], $valid_results)) {
                throw new InvalidArgumentException('A valid result was not provided.', 400);
            }
            
            $this->review->result = $post['result'];
        }

        if ($this->review->status == Review::STATUS_REVIEW_FINISHED && empty($this->review->date_reviewed)) {
            $this->review->date_reviewed = Util::epochToDateTime();
        }
        
        $this->review->date_edited          = Util::epochToDateTime();
        $this->review->last_edited_users_id = $this->current_user->id;
        $this->review->save();

        Controller::redirect(
            $this->getEditURL(),
            new FlashBagMessage(FlashBagMessage::TYPE_SUCCESS, 'Review updated')
        );
    }

    /**
     * handle the delete post action
     *
     * @param $get
     * @param $post
     * @param $files
     * @throws \SiteMaster\Core\RuntimeException
     */
    protected function delete($get, $post, $files)
    {
        if (!$this->review->delete()) {
            throw new RuntimeException('Unable to delete the review', 400);
        }

        Controller::redirect(
            Config::get('URL'),
            new FlashBagMessage(FlashBagMessage::TYPE_SUCCESS, 'Review deleted')
        );
    }

    /**
     * Return a list of all reviewable sites
     * 
     * @return All
     */
    public function getReviewableSites()
    {
        return new All();
    }

    public function getEditURL()
    {
        return $this->getURL();
    }
}
