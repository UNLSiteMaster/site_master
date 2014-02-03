<?php
namespace SiteMaster\Core\Events\User;

use Symfony\Component\EventDispatcher\Event;

class Search extends Event
{
    const EVENT_NAME = 'user.search';

    protected $results = array();
    
    protected $term = '';
    
    public function __construct($term)
    {
        $this->term = $term;
    }
    
    public function getSearchTerm()
    {
        return $this->term;
    }

    public function getResults()
    {
        return $this->results;
    }
    
    public function addResult($provider, $uid, $email, $first_name, $last_name)
    {
        $this->results[$provider.'?'.$uid] =  array(
            'provider'   => $provider,
            'uid'        => $uid,
            'email'      => $email,
            'first_name' => $first_name,
            'last_name'  => $last_name
        );
    }
}
