<?php
namespace SiteMaster\Core\Registry\Query;

use SiteMaster\Core\InvalidArgumentException;

class Result extends \IteratorIterator
{
    function __construct($options = array())
    {
        if (!isset($options['result'])) {
            throw new InvalidArgumentException('You must pass a result', 400);
        }

        parent::__construct($options['result']);
    }
}
