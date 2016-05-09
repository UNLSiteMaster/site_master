<?php
namespace SiteMaster\Core\Auditor\Parser;

class HTML5 extends \Spider_Parser
{
    public function __construct($options = array())
    {
        parent::__construct($options);
    }

    protected function getXPath($content)
    {
        $document = new \DOMDocument();
        $document->strictErrorChecking = false;

        if (function_exists('mb_convert_encoding')) {
            //Ensure content is UTF-8, if it isn't, loadXML might not work.
            $content = mb_convert_encoding($content, 'UTF-8');
        }
        
        $html5 = new \Masterminds\HTML5();
        $document = $html5->loadHTML($content);

        $xpath = new \DOMXPAth($document);
        $xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');

        return $xpath;
    }
}
