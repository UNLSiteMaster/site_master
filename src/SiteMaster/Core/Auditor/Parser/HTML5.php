<?php
namespace SiteMaster\Core\Auditor\Parser;

class HTML5 extends \Spider_Parser
{
    public function __construct($options = array())
    {
        $options = array_replace_recursive($options, array(
            'tidy_config' => array(
                'new-blocklevel-tags' => 'article,header,footer,section,nav,main,aside,figure,figcaption',
                'new-inline-tags'     => 'video,audio,canvas,ruby,rt,rp,track,mark,meter,time',
            )
        ));
        
        parent::__construct($options);
    }

    protected function getXPath($content)
    {
        $document = new \DOMDocument();
        $document->strictErrorChecking = false;

        if ($this->options['tidy'] && extension_loaded('tidy')) {
            //Convert and repair as xhtml
            $tidy     = new \tidy;
            $content = $tidy->repairString($content, $this->options['tidy_config']);
        }

        if (function_exists('mb_convert_encoding')) {
            //Ensure content is UTF-8, if it isn't, loadXML might not work.
            $content = mb_convert_encoding($content, 'UTF-8');
        }

        //Skip application/ld+json scripts as they break libxml. TODO: don't use libxml. :p
        $content = preg_replace('/<script type=\"application\/ld\+json\">.*?<\/script>/s','<!-- script application/ld+json removed by sitemaster -->',$content);

        $document->loadXML(
            $content,
            LIBXML_NOERROR | LIBXML_NONET | LIBXML_NOWARNING | LIBXML_NOCDATA
        );

        $xpath = new \DOMXPAth($document);
        $xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');

        return $xpath;
    }
}
