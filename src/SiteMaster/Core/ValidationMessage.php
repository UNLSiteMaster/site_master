<?php
namespace SiteMaster\Core;

class ValidationMessage
{
    public $type = "";
    public $messages = array();
    
    const TYPE_ERROR = 'error';

    public function __construct($messages) {
        $this->type = self::TYPE_ERROR;
        $this->messages = $messages;
    }
}