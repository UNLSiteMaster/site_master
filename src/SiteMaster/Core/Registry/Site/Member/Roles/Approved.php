<?php
namespace SiteMaster\Core\Registry\Site\Member\Roles;

class Approved extends All
{
    public function __construct(array $options = array())
    {
        $options['approved'] = 'YES';
        parent::__construct($options);
    }
}
