<?php
namespace SiteMaster\Core;

class FlashBagMessage
{
    public $type = "";
    public $message = "";
    
    const TYPE_SUCCESS = 'success';
    const TYPE_ERROR = 'error';
    const TYPE_INFO = 'info';

    public function __construct($type, $message) {
        $this->type = $type;
        $this->message = $message;
    }
}