<?php
namespace SiteMaster;

class FlashBagMessage
{
    public $type = "";
    public $message = "";

    public function __construct($type, $message) {
        $this->type = $type;
        $this->message = $message;
    }
}