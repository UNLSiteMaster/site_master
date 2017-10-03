<?php
namespace SiteMaster\Core\Auditor;

use DOMXPath;
use Spider_Filter_Anchor;
use Spider_Filter_EffectiveURI;
use Spider_Filter_Empty;
use Spider_Filter_External;
use Spider_Filter_JavaScript;
use Spider_Filter_Mailto;
use Spider_Filter_RobotsTxt;
use Spider_UriIterator;

/**
 * We are overriding the Spider class so that we can augment the functionality with data that is rendered by the DOM
 * 
 * Specifically, the php DOM implementation does not render JS, so in order to handle single page applications and dynamic content, we are using URIs as provided by an external interface.
 * 
 * We still want the Spider to filter the URIs for different circumstances.
 * 
 * Class Spider
 * @package SiteMaster\Core\Auditor
 */
class Spider extends \Spider
{
    /**
     * @var array $uris
     */
    protected static $uris = [];

    /**
     * Set the URIs to use internally
     * 
     * @param array $uris
     */
    public static function setURIs(array $uris)
    {
        self::$uris = $uris;
    }
    
    /**
     * Returns all valid uris for a page
     * 
     * Overriden so that self::$uris is used instead of parsing the DOM
     *
     * @param string   $baseUri    - the base uri for the page (NOT the site base)
     * @param string   $currentUri - the uri of the document
     * @param DOMXPath $xpath      - the xpath for the document
     *
     * @return Spider_UriIterator - a list of uris
     */
    public static function getUris($baseUri, $currentUri, DOMXPath $xpath)
    {
        return new Spider_UriIterator(self::$uris);
    }

    /**
     * Get all crawlable uris for a page
     * crawlable uris are URIs that that the spider can crawl
     *
     * This removes anchors, empty uris, javascipr and mailto calls, external uris, and uris that return a 404
     *
     * It will also get the effective URIs for a uri (the final uri if it redirects)
     *
     * @param          $startUri   - the base uri for the site
     * @param string   $baseUri    - the base uri for the page
     * @param string   $currentUri - the current uri to get URIs from
     * @param DOMXPath $xpath      - the DOMXPath object for the current uri
     *
     * @return Spider_UriIterator - a list of uris
     */
    public function getCrawlableUris($startUri, $baseUri, $currentUri, DOMXPath $xpath)
    {
        $uris = self::getUris($baseUri, $currentUri, $xpath);

        //remove anchors
        $uris = new Spider_Filter_Anchor($uris);

        //remove empty uris
        $uris = new Spider_Filter_Empty($uris);

        //remove javascript
        $uris = new Spider_Filter_JavaScript($uris);

        //remove mailto links
        $uris = new Spider_Filter_Mailto($uris);

        //Filter external links out. (do now to reduce the number of HTTP requests that we have to make)
        $uris = new Spider_Filter_External($uris, $startUri);

        if ($this->options['use_effective_uris']) {
            //Get the effective URIs
            $uris = new Spider_Filter_EffectiveURI($uris, $this->options['curl_options']);

            //Filter external links again as they may have changed due to the effectiveURI filter.
            $uris = new Spider_Filter_External($uris, $startUri);
        }

        if ($this->options['respect_robots_txt']) {
            //Filter out pages that are disallowed by robots.txt
            $uris = new Spider_Filter_RobotsTxt($uris, $this->options);
        }

        return $uris;
    }
}