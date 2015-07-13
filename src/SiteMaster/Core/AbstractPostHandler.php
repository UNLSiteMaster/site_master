<?php
namespace SiteMaster\Core;

abstract class AbstractPostHandler implements PostHandlerInterface
{
    protected $errors = array();
    
    public function elementHasError($element_id)
    {
        if (isset($this->errors[$element_id])) {
            return true;
        }
    }

    /**
     * @param string $element the element ID, without a leading #
     * @param string $error_message
     */
    public function addError($element, $error_message)
    {
        $this->errors[$element] = $error_message;
    }
    
    public function sendErrorMessage()
    {
        $message = new ValidationMessage($this->errors);
        Controller::addValidationMessage($message);
    }
}