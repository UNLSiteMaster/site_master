<?php
namespace SiteMaster\Core\Registry\Site\Member\Roles;

class Pending extends All
{
    public function __construct(array $options = array())
    {
        $options['approved'] = 'NO';
        parent::__construct($options);
    }
}