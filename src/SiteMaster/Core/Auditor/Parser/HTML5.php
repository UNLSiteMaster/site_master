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
}
