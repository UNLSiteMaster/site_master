<?php
namespace SiteMaster\Plugins\Metric_links;

use Guzzle\Http\Exception\CurlException;
use Guzzle\Http\Exception\MultiTransferException;
use SiteMaster\Core\Auditor\Logger\Metrics;
use SiteMaster\Core\Auditor\Metric\Mark;
use SiteMaster\Core\Auditor\Metric\Marks\UniqueValueFound;
use SiteMaster\Core\Auditor\MetricInterface;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\Auditor\Scan;
use SiteMaster\Core\Auditor\Site\Page;
use Guzzle\Http\Client;
use SiteMaster\Core\Util;

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
            'request_options' => array( //Guzzle request options
                'timeout' => 5,
                'connect_timeout' => 5,
                'allow_redirects' => false
            ),
            'chunks' => 10, //The number of URLs to request at once
            'grading_method' => self::GRADE_METHOD_DEFAULT,
            'http_error_codes' => array(
                301,
                400, 402, 403, 404,
                500, 501, 502, 503
            ),
            'message_text' => array(
                'link_connection_error_3' => 'The URL is malformed',
                'link_connection_error_6' => 'Could not resolve host',
                'link_connection_error_7' => 'Failed to connect to host or proxy',
                'link_http_code_301' => 'Moved Permanently',
                'link_http_code_400' => 'Bad Request',
                'link_http_code_402' => 'Payment Required',
                'link_http_code_403' => 'Forbidden',
                'link_http_code_404' => 'Not Found',
                'link_http_code_500' => 'Internal Server Error',
                'link_http_code_501' => 'Not Implemented',
                'link_http_code_502' => 'Bad Gateway',
                'link_http_code_503' => 'Service Unavailable',
            ),
            'help_text' => array(),
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
        $links = $this->getLinks($uri, $xpath);
        
        $this->markPage($page, $links);
        
        return true;
    }

    /**
     * This method will find broken links and mark a page appropriately
     * 
     * @param Page $page the page to mark
     * @param array $links an array of the links found on the page
     */
    public function markPage(Page $page, $links)
    {
        $statuses = $this->getStatuses(array_unique($links));
        $counts = array_count_values($links);

        foreach ($statuses as $url=>$status) {
            if (!$this->isError($status)) {
                //don't mark it...
                continue;
            }

            $machine_name = $this->getMachineNameForStatus($status);
            $message      = $this->getStatusMessage($machine_name);
            $help_text    = $this->getStatusHelpText($machine_name);
            $points       = $this->getPointDeduction($status->http_code);

            $mark = $this->getMark($machine_name, $message, $points, null, $help_text);

            for ($i = 1; $i <= $counts[$url]; $i++) {
                //Add it for every instance of the link found on the page.
                $page->addMark($mark, array(
                    'value_found' => $url
                ));
            }
        }
    }

    /**
     * get the message for a status to be used with a mark
     * 
     * @param string $machine_name the machine name of the mark
     * @return string
     */
    public function getStatusMessage($machine_name)
    {
        if (isset($this->options['message_text'][$machine_name])) {
            return $this->options['message_text'][$machine_name];
        }
        
        return 'General Connection error';
    }

    /**
     * get the help text to be used with a mark for a given machine name
     * 
     * @param string $machine_name the machine name of the mark
     * @return string
     */
    public function getStatusHelpText($machine_name)
    {
        if (isset($this->options['help_text'][$machine_name])) {
            return $this->options['help_text'][$machine_name];
        }

        return 'Update or remove this link';
    }

    /**
     * Determine if a status is an error and should be logged
     * 
     * @param LinkStatus $status
     * @return bool
     */
    public function isError(LinkStatus $status)
    {
        if (in_array($status->http_code, $this->options['http_error_codes'])) {
            return true;
        }

        if ($status->curl_code && empty($status->http_code)) {
            return true;
        }
        
        return false;
    }

    /**
     * Get the machine name for a status
     * 
     * @param LinkStatus $status
     * @return string
     */
    public function getMachineNameForStatus(LinkStatus $status)
    {
        if ($status->curl_code && empty($status->http_code)) {
            return 'link_connection_error_' . $status->curl_code;
        }
        
        return 'link_http_code_' . $status->http_code;
    }

    /**
     * Get the links for a page
     * 
     * @param string $uri the uri of the page to get links for
     * @param \DOMXPath $xpath the xpath of the page
     * @return array an array of links
     */
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
     * @param string $http_code get the point deduction for the current grading method and http code
     * @return int
     */
    public function getPointDeduction($http_code)
    {
        switch ($this->options['grading_method'])
        {
            case self::GRADE_METHOD_DEFAULT:
                if ($http_code >= 400) {
                    //error
                    return 20;
                }

                if ($http_code == 301) {
                    //Redirect
                    return 5;
                }

                //Connection problems
                return 15;
            case self::GRADE_METHOD_NUMBER_OF_LINKS:
                if ($http_code >= 400) {
                    //error
                    return 2;
                }

                if ($http_code == 301) {
                    //Redirect
                    return 1;
                }

                //Connection problems
                return 2;
            case self::GRADE_METHOD_PASS_FAIL:
                return 1;
        }
    }

    /**
     * Strip fragments for URIs
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

    /**
     * Get the http statuses for a set of links.  This will check for cached statuses before sending requests.
     * 
     * @param array $links the links to check
     * @return array an associative array of URLs and LinkStatuses
     */
    public function getStatuses(array $links)
    {
        $statuses = array();
        
        foreach ($links as $key=>$link) {
            if ($status = LinkStatus::getByURL($link)) {
                $statuses[$link] = $status;
                unset($links[$key]);
            }
        }
        
        //divide the links into groups to 10 to check
        $chunks = array_chunk($links, $this->options['chunks']);
        
        foreach ($chunks as $chunk) {
            $statuses = array_merge($this->getHTTPStatus($chunk), $statuses);
            sleep(1);
        }
        
        return $statuses;
    }

    /**
     * Get the http statuses for a set of links
     *
     * @param array $links the links to check
     * @return array an associative array of URLs and LinkStatuses
     */
    public function getHTTPStatus($links)
    {
        $client = new Client();
        $statuses = array();
        $requests = array();
        
        try {
            foreach ($links as $link) {
                $requests[] = $client->head($link, array(), $this->options['request_options']);
            }

            $responses = $client->send($requests);
            
            foreach ($responses as $response) {
                $url = $response->getEffectiveUrl();
                $statuses[$url] = LinkStatus::createLinkStatus($url, $response->getStatusCode(), 0);
            }

            return $statuses; //We made it this far, so no links failed
        } catch (MultiTransferException $e) {
            foreach ($e->getFailedRequests() as $request) {
                $curl_code         = 0;
                $http_code         = null;
                $url               = $request->getURL();

                $exception = $e->getExceptionForFailedRequest($request);
                if ($exception instanceof CurlException) {
                    $curl_code = $exception->getErrorNo();
                }

                if ($response = $request->getResponse()) {
                    $http_code = $request->getResponse()->getStatusCode();
                }

                $statuses[$url] = LinkStatus::createLinkStatus($url, $http_code, $curl_code);
            }

            foreach ($e->getSuccessfulRequests() as $request) {
                $response = $request->getResponse();
                $url = $response->getEffectiveUrl();
                $statuses[$url] = LinkStatus::createLinkStatus($url, $response->getStatusCode(), 0);
            }
        }
        
        return $statuses;
    }
}