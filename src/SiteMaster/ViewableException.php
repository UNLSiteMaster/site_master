<?php
namespace SiteMaster;

class ViewableException extends \Exception implements ViewableInterface
{
    public function __construct($message = "", $code = 0, $previous = NULL) {
        parent::__construct($message, $code, $previous);
    }

    public function render()
    {
        $array = array();

        $array['message'] = $this->message;
        $array['code']    = $this->code;

        return $array;
    }

    public function getPageTitle()
    {
        return "Error";
    }

    public function getURL()
    {
        return "";
    }
}