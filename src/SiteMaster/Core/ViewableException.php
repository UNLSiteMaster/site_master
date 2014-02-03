<?php
namespace SiteMaster\Core;

class ViewableException extends \Exception implements ViewableInterface
{
    public function __construct($message = "", $code = 0, $previous = NULL)
    {
        parent::__construct($message, $code, $previous);
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