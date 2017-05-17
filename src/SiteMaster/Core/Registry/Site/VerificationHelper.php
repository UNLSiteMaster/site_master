<?php
namespace SiteMaster\Core\Registry\Site;

use SiteMaster\Core\AccessDeniedException;
use SiteMaster\Core\Auditor\Parser\HTML5;
use SiteMaster\Core\InvalidArgumentException;
use SiteMaster\Core\Controller;
use SiteMaster\Core\FlashBagMessage;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\RuntimeException;
use SiteMaster\Core\UnexpectedValueException;
use Sitemaster\Core\User\Session;
use SiteMaster\Core\Util;
use SiteMaster\Core\ViewableInterface;
use SiteMaster\Core\PostHandlerInterface;
use SiteMaster\Core\User\User;

class VerificationHelper
{
    public function verifyByMetaTag($html)
    {
        $parser = new HTML5();
        $xpath = $parser->parse($html);

        $nodes = $xpath->query(
            "//xhtml:meta[@name='sitemaster-verification-code']/@content | //meta[@name='sitemaster-verification-code']/@content"
        );
        
        $codes = [];
        foreach ($nodes as $node) {
            $codes[] = $node->nodeValue;
        }
        
        return $codes;
    }
}
