<?php
namespace SiteMaster\Core\Auditor\Scan;

use SiteMaster\Core\EmailInterface;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\Auditor\Scan;

class ChangedEmail implements EmailInterface
{

    /**
     * @var Scan
     */
    public $scan = null;
    
    public function __construct(Scan $scan)
    {
        $this->scan = $scan;
    }

    /**
     * Get the To address
     *
     * Expected to return a email address as a string, or an array of array('email@example.org' => 'Name');
     *
     * @return array|string
     */
    public function getTo()
    {
        $site = $this->scan->getSite();

        $emails = array();
        
        foreach ($site->getMembers() as $member) {
            /**
             * @var $member \SiteMaster\Core\Registry\Site\Member
             */
            foreach ($member->getRoles() as $member_role) {
                /**
                 * @var $member_role \SiteMaster\Core\Registry\Site\Member\Role
                 */
                $role = $member_role->getRole();
                
                if (!$member_role->isApproved()) {
                    continue;
                }

                /**
                 * @var $role \SiteMaster\Core\Registry\Site\Role
                 */
                if (!in_array($role->role_name, array('developer', 'admin'))) {
                    //Only send to developers and admin
                    continue;
                }

                $user = $member->getUser();
                
                if (empty($user->email)) {
                    //Doesn't have an email
                    continue;
                }

                if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                    //Doesn't have a valid email
                    continue;
                }
                
                //add to the list of emails to send
                $emails[$user->email] =  $user->getName();
            }
        }
        
        return $emails;
    }

    /**
     * Get the Subject of the email
     *
     * @return mixed
     */
    public function getSubject()
    {
        return 'We found some changes!';
    }
}
