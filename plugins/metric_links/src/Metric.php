<?php
namespace SiteMaster\Plugins\Metric_links;

use Guzzle\Http\Exception\CurlException;
use Guzzle\HTTP\Exception\MultiTransferException;
use SiteMaster\Core\Auditor\Logger\Metrics;
use SiteMaster\Core\Auditor\MetricInterface;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\Auditor\Scan;
use SiteMaster\Core\Auditor\Site\Page;
use Guzzle\Http\Client;

class Metric extends MetricInterface
{
    /**
     * Default grading method.
     * Start with 100 points.
     * Subtract 20 for every 4**, or 5** (exuding 401 errors)
     * Subtract 15 for every connection error
     * Subtract 5 for every 301
     */
    const GRADE_METHOD_DEFAULT = 1;

    /**
     * Grade based on the total number of links
     * points available = 2*total number of links on the page
     * grading method:
     * Subtract 2 points for every 4**, 5** or connection error
     * Subtract 1 point for every 301
     */
    const GRADE_METHOD_NUMBER_OF_LINKS = 2;

    /**
     * Grade as pass fail.
     * 4**, 5** and connection errors will cause the metric to fail
     */
    const GRADE_METHOD_PASS_FAIL = 3;

    /**
     * @param string $plugin_name
     * @param array $options
     */
    public function __construct($plugin_name, array $options = array())
    {
        $options = array_merge_recursive($options, array(
            'filters' => array(
                '\\SiteMaster\\Plugins\\Metric_links\\Filters\\Scheme',
                '\\SiteMaster\\Plugins\\Metric_links\\Filters\\InvalidURI',
            ),
            'grading_method' => self::GRADE_METHOD_DEFAULT,
            'http_error_codes' => array(
                400, 402, 403, 404,
                500, 501, 502, 503
            )
        ));
        
        parent::__construct($plugin_name, $options);
    }

    /**
     * Get the human readable name of this metric
     *
     * @return string The human readable name of the metric
     */
    public function getName()
    {
        return 'Link Checker';
    }

    /**
     * Get the Machine name of this metric
     *
     * This is what defines this metric in the database
     *
     * @return string The unique string name of this metric
     */
    public function getMachineName()
    {
        return 'link_checker';
    }

    /**
     * Determine if this metric should be graded as pass-fail
     *
     * @return bool True if pass-fail, False if normally graded
     */
    public function isPassFail()
    {
        if ($this->options['grading_method'] == self::GRADE_METHOD_PASS_FAIL) {
            return true;
        }
        
        return false;
    }

    /**
     * Scan a given URI and apply all marks to it.
     *
     * All that this
     *
     * @param string $uri The uri to scan
     * @param \DOMXPath $xpath The xpath of the uri
     * @param int $depth The current depth of the scan
     * @param \SiteMaster\Core\Auditor\Site\Page $page The current page to scan
     * @param \SiteMaster\Core\Auditor\Logger\Metrics $context The logger class which calls this method, you can access the spider, page, and scan from this
     * @return bool True if there was a successful scan, false if not.  If false, the metric will be graded as incomplete
     */
    public function scan($uri, \DOMXPath $xpath, $depth, Page $page, Metrics $context)
    {
        $page = $context->getPage();
        
        $mark = $this->getMark('test', 'Just a test', 1.5);
        
        $page->addMark($mark);
        
        return true;
    }
    
    public function getLinks($uri, \DOMXPath $xpath)
    {
        $links = \Spider::getUris(\Spider::getUriBase($uri), $uri, $xpath);

        //Filter the links
        foreach ($this->options['filters'] as $filter_class) {
            $links = new $filter_class($links);
        }
        
        $links_array = array();
        foreach ($links as $link) {
            $links_array[] = $this->stripURIFragment($link);
        }

        return $links_array;
    }

    /**
     * Strip fragments for UIRIs
     *
     * This is used when getting the status code for a URI.
     * Some environments return 404 for every URI with a #fragment
     *
     * @param string $uri
     * @return string the new URI
     */
    function stripURIFragment($uri) {
        $parts = explode('#', $uri, 2);

        if (isset($parts[0])) {
            return $parts[0];
        }

        return $uri;
    }
    
    public function checkLinks($links)
    {
        $bad_links = array();
        //TODO: check for pre-existing marks for each link before sending a curl request
        
        //TODO: check to see if we already scanned it this run
        
        //TODO: send batches of curl requests, limit them to 10 at a time, then wait 1 second
        
        
        
        return $bad_links;
    }
    
    public function getHTTPStatus($links)
    {
        $client = new Client();
        $bad_links = array();
        $requests = array();
        
        try {
            foreach ($links as $link) {
                $requests[] = $client->head($link, array(), array(
                    'timeout' => 5,
                    'connect_timeout' => 5,
                    'allow_redirects' => false
                ));
            }

            $client->send($requests);

            return $bad_links; //We made it this far, so no links failed
        } catch (MultiTransferException $e) {
            foreach ($e->getFailedRequests() as $request) {
                $curl_code        = 0;
                $curl_message     = '';
                $http_code        = null;
                $connection_error = false;

                $exception = $e->getExceptionForFailedRequest($request);
                if ($exception instanceof CurlException) {
                    $curl_code = $exception->getErrorNo();
                    $curl_message = $exception->getError();
                }

                if ($response = $request->getResponse()) {
                    $http_code = $request->getResponse()->getStatusCode();
                } else {
                    $connection_error = true;
                }

                //Determine if we need to store this request as an error
                if ($connection_error || in_array($http_code, $this->options['http_error_codes'])) {
                    $bad_links[] = new BadLInk($request->getURL(), $http_code, $connection_error, $curl_code, $curl_message);
                }
            }
        }
    }
}