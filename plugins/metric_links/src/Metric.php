<?php
namespace SiteMaster\Plugins\Metric_links;

use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Exception\CurlException;
use Guzzle\Http\Exception\MultiTransferException;
use Guzzle\Http\Exception\RequestException;
use SiteMaster\Core\Auditor\Logger\Metrics;
use SiteMaster\Core\Auditor\Metric\Mark;
use SiteMaster\Core\Auditor\Metric\Marks\UniqueValueFound;
use SiteMaster\Core\Auditor\MetricInterface;
use SiteMaster\Core\Config;
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
     * Subtract 0 point for every 301
     */
    const GRADE_METHOD_DEFAULT = 1;

    /**
     * Grade based on the total number of links
     * points available = 2*total number of links on the page
     * grading method:
     * Subtract 2 points for every 4**, 5** or connection error
     * Subtract 0 point for every 301
     */
    const GRADE_METHOD_NUMBER_OF_LINKS = 2;

    /**
     * Grade as pass fail.
     * 4**, 5** and connection errors will cause the metric to fail
     */
    const GRADE_METHOD_PASS_FAIL = 3;

    const MARK_LINK_LIMIT_HIT = 'mark_link_limit_hit';

    /**
     * @param string $plugin_name
     * @param array $options
     */
    public function __construct($plugin_name, array $options = array())
    {
        // Set up list of suppressed domains
        $suppressed_domains = array();
        $suppressed_domains_filename = dirname(__DIR__) . '/data/suppressed_domains.txt';
        if (file_exists($suppressed_domains_filename)) {
            $suppressed_domains_file_contents = file_get_contents($suppressed_domains_filename);
            $exploded_domains = explode("\n", $suppressed_domains_file_contents);

            foreach ($exploded_domains as $single_domain) {
                $suppressed_domains[] = trim($single_domain);
            }
        }

        $options = array_replace_recursive(array(
            'grading_method' => self::GRADE_METHOD_DEFAULT,
            'http_error_codes' => array(
                301,
                400, 402, 403, 404,
                500, 501, 502, 503 //500 level errors are included because they impose a bad user experience, and appear 'broken' to end users
            ),
            'message_text' => array(
                'link_connection_error_3' => 'The URL is malformed',
                'link_connection_error_6' => 'Could not resolve host',
                'link_connection_error_7' => 'Failed to connect to host or proxy',
                'link_connection_error_28' => 'Connecting to this link timed out',
                'link_http_code_301' => 'Moved Permanently (301)',
                'link_http_code_400' => 'Bad Request (400)',
                'link_http_code_402' => 'Payment Required (402)',
                'link_http_code_403' => 'Forbidden (403)',
                'link_http_code_404' => 'Not Found (404)',
                'link_http_code_500' => 'Internal Server Error (500)',
                'link_http_code_501' => 'Not Implemented (501)',
                'link_http_code_502' => 'Bad Gateway (502)',
                'link_http_code_503' => 'Service Unavailable (503)',
                'link_http_code_after_redirect_400' => 'Bad Request, After Redirect (400)',
                'link_http_code_after_redirect_402' => 'Payment Required, After Redirect (402)',
                'link_http_code_after_redirect_403' => 'Forbidden, After Redirect (403)',
                'link_http_code_after_redirect_404' => 'Not Found, After Redirect (404)',
                'link_http_code_after_redirect_500' => 'Internal Server Error, After Redirect (500)',
                'link_http_code_after_redirect_501' => 'Not Implemented, After Redirect (501)',
                'link_http_code_after_redirect_502' => 'Bad Gateway, After Redirect (502)',
                'link_http_code_after_redirect_503' => 'Service Unavailable, After Redirect (503)',
                self::MARK_LINK_LIMIT_HIT => 'Link limit was hit, not all links were scanned',
            ),
            'help_text' => array(
                'link_connection_error_3' => 'Ensure that the URL is correct',
                'link_connection_error_6' => 'The link my contain typos.',
                'link_connection_error_7' => 'Failed to connect to host or proxy',
                'link_connection_error_28' => 'Please make sure that the link still works.  You may need to contact the server administrator to fix the problem.',
                'link_http_code_301' => 'The content that this link points to has moved. It is a best practice to update your links to the new URL, as the old one might stop working. You can find the new URL by clicking the link (your browser should redirect you to the new URL)',
                'link_http_code_400' => 'The remote server did not understand the link.  Either fix the link or remove it.',
                'link_http_code_402' => 'Payment Required',
                'link_http_code_403' => 'The content that this link points to requires authorization to access.  Please ensure that this is not a mistake and that there is enough context to help the user gain access if they need to.',
                'link_http_code_404' => 'The content that this link points to no longer exists.  Please remove this link.',
                'link_http_code_500' => 'The server is returning an error for this link.  This may be resolved in time without any action on your part, but it might be worth while to contact the server\'s administrator or remove/update this link.',
                'link_http_code_501' => 'Not Implemented',
                'link_http_code_502' => 'Bad Gateway',
                'link_http_code_503' => 'This will usually get resolved without any need for action on your part.  If not, you will have to contact the server administrator or remove this link.',
                'link_http_code_after_redirect_400' => '(After the redirect) The remote server did not understand the link.  Either fix the link or remove it.',
                'link_http_code_after_redirect_402' => '(After the redirect) Payment Required',
                'link_http_code_after_redirect_403' => '(After the redirect) The content that this link points to requires authorization to access.  Please ensure that this is not a mistake and that there is enough context to help the user gain access if they need to.',
                'link_http_code_after_redirect_404' => '(After the redirect) The content that this link points to no longer exists.  Please remove this link.',
                'link_http_code_after_redirect_500' => '(After the redirect) The server is returning an error for this link.  This may be resolved in time without any action on your part, but it might be worth while to contact the server\'s administrator or remove/update this link.',
                'link_http_code_after_redirect_501' => '(After the redirect) Not Implemented',
                'link_http_code_after_redirect_502' => '(After the redirect) Bad Gateway',
                'link_http_code_after_redirect_503' => '(After the redirect) This will usually get resolved without any need for action on your part.  If not, you will have to contact the server administrator or remove this link.',
                self::MARK_LINK_LIMIT_HIT => 'The link limit was hit, so not all links on the page were scanned. You will have to manually check links on the page.',
            ),
            'suppressed_domains' => $suppressed_domains,
        ), $options);

        parent::__construct($plugin_name, $options);
    }

    /**
     *  This will allow custom overrides manually defined in overrides table to be honored.
     *
     * @return bool
     */
    public function allowCustomOverridingErrors()
    {
        return true;
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
        $this->markPage($page);

        return true;
    }

    /**
     * This method will find broken links and mark a page appropriately
     *
     * @param Page $page the page to mark
     */
    public function markPage(Page $page)
    {
        $links = $page->getLinks();

        foreach ($links as $link) {

            // Ignore suppressed domains
            if ($this->isSuppressedDomain($link)) {
                continue;
            }

            // If we get an error after the redirect
            // If the final status code is a redirect that means the redirect chain was too long and we don't care about it anymore just give them a notice
            if ($link->isRedirect() && !$link->isFinalRedirect() && $this->isFinalError($link)) {
                $machine_name = $this->getMachineNameForFinalStatus($link);
                $message      = $this->getStatusMessage($machine_name);
                $help_text    = $this->getStatusHelpText($machine_name);
                $points       = $this->getPointDeduction($link->final_status_code);

                $allows_perm_override = false;
                if ($points === 0) {
                    //Allow notices for this metric to be permanently overridden, because they likely do not need to be reviewed again.
                    $allows_perm_override = true;
                }
                $mark = $this->getMark($machine_name, $message, $points, null, $help_text, $allows_perm_override);

                $value_found = $link->original_url . ' which redirected to ' . $link->final_url;

                $page->addMark($mark, array(
                    'value_found' => $value_found
                ));

            // If we had a non-200 status code
            } elseif ($this->isOriginalError($link)) {
                $machine_name = $this->getMachineNameForOriginalStatus($link);
                $message      = $this->getStatusMessage($machine_name);
                $help_text    = $this->getStatusHelpText($machine_name);
                $points       = $this->getPointDeduction($link->original_status_code);

                $allows_perm_override = false;
                if ($points === 0) {
                    //Allow notices for this metric to be permanently overridden, because they likely do not need to be reviewed again.
                    $allows_perm_override = true;
                }
                $mark = $this->getMark($machine_name, $message, $points, null, $help_text, $allows_perm_override);

                $value_found = $link->original_url;
                if ($link->isRedirect()) {
                    $value_found .= ' which redirected to ' . $link->final_url;
                }

                $page->addMark($mark, array(
                    'value_found' => $value_found
                ));
            }
        }

        if (Page::LIMIT_LIMIT_HIT_YES == $page->link_limit_hit) {
            //add a notice that we did not check all of the links on the page
            $message = $this->getStatusMessage(self::MARK_LINK_LIMIT_HIT);
            $help_text = $this->getStatusHelpText(self::MARK_LINK_LIMIT_HIT);
            $mark = $this->getMark(self::MARK_LINK_LIMIT_HIT, $message, 0, null, $help_text);
            $page->addMark($mark, array(
                'value_found' => 'Link limit is: ' . Config::get('LINK_SCAN_LIMIT')
            ));
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
     * Determine if an original status is an error and should be logged
     *
     * @param Page\Link $link
     * @internal param Page\Link $status
     * @return bool
     */
    public function isOriginalError(Page\Link $link)
    {
        if ($link->isCurlError()) {
            return true;
        }

        if (in_array($link->original_status_code, $this->options['http_error_codes'])) {
            return true;
        }

        return false;
    }

    /**
     * Determine if a final status after redirect is an error and should be logged
     *
     * @param Page\Link $link
     * @internal param Page\Link $status
     * @return bool
     */
    public function isFinalError(Page\Link $link)
    {
        if ($link->isCurlError()) {
            return true;
        }

        if (in_array($link->final_status_code, $this->options['http_error_codes'])) {
            return true;
        }

        return false;
    }

    /**
     * Determine if a URL matches a suppressed domain
     *
     * @param Page\Link $link
     * @internal param Page\Link $original_url
     * @internal param Page\Link $final_url
     * @return bool
     */
    public function isSuppressedDomain(Page\Link $link) {
        $suppressed_domain_check = false;
        $final_URL_obj = parse_url($link->final_url);
        $starting_URL_obj = parse_url($link->original_url);

        foreach($this->options['suppressed_domains'] as $suppressed_domains) {
            if (
                strpos($final_URL_obj['host'], $suppressed_domains) !== false ||
                strpos($starting_URL_obj['host'], $suppressed_domains) !== false
            ) {
                $suppressed_domain_check = true;
            }
        }

        return $suppressed_domain_check;
    }

    /**
     * Get the machine name for a original status
     *
     * @param Page\Link $link
     * @internal param Page\Link $status
     * @return string
     */
    public function getMachineNameForOriginalStatus(Page\Link $link)
    {
        if ($link->isCurlError()) {
            return 'link_connection_error_' . $link->original_curl_code;
        }

        return 'link_http_code_' . $link->original_status_code;
    }

    /**
     * Get the machine name for a final status after the redirect
     *
     * @param Page\Link $link
     * @internal param Page\Link $status
     * @return string
     */
    public function getMachineNameForFinalStatus(Page\Link $link)
    {
        if ($link->isCurlError()) {
            return 'link_connection_error_' . $link->final_curl_code;
        }

        return 'link_http_code_after_redirect_' . $link->final_status_code;
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
                if ($http_code == 301) {
                    //Redirect
                    return 0;
                }

                if ($http_code == 403) {
                    //These can be legitimate, show them as a notice
                    return 0;
                }

                if ($http_code >= 400) {
                    //error
                    return 20;
                }


                //Connection problems (zero points because it is probably our fault)
                return 0;
            case self::GRADE_METHOD_NUMBER_OF_LINKS:
                if ($http_code == 301) {
                    //Redirect
                    return 0;
                }

                if ($http_code == 403) {
                    //These can be legitimate, show them as a notice
                    return 0;
                }

                if ($http_code >= 400) {
                    //error
                    return 2;
                }

                if ($http_code == 0) {
                    //Connection problems (zero points because it is probably our fault)
                    return 0;
                }

                //Connection problems
                return 1;
            case self::GRADE_METHOD_PASS_FAIL:
                if ($http_code == 301) {
                    //Redirect
                    return 0;
                }

                if ($http_code == 403) {
                    //These can be legitimate, show them as a notice
                    return 0;
                }

                if ($http_code == 0) {
                    //Connection problems (zero points because it is probably our fault)
                    return 0;
                }

                return 1;
        }
    }
}
