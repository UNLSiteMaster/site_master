<?php
namespace SiteMaster\Core\Auditor\Downloader;

use SiteMaster\Core\Auditor\Scan;
use SiteMaster\Core\Auditor\Site\Page;
use SiteMaster\Core\Config;
use SiteMaster\Core\HTTPConnectionException;
use SiteMaster\Core\Registry\Registry;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\UnexpectedValueException;

class HTMLOnly extends \Spider_Downloader
{
    private $curl = null;

    /**
     * @var bool|Site
     */
    protected $site = false;

    /**
     * @var bool|Page
     */
    protected $page = false;

    /**
     * @var bool|Scan
     */
    protected $scan = false;

    /**
     * Set up the HTMLOnly download
     */
    public function __construct(Site $site, Page $page, Scan $scan)
    {
        $this->curl = curl_init();
        
        $this->site = $site;
        $this->page = $page;
        $this->scan = $scan;

        curl_setopt_array(
            $this->curl,
            array(
                CURLOPT_AUTOREFERER    => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_CONNECTTIMEOUT => 60,
                CURLOPT_TIMEOUT        => 60,
                CURLOPT_USERAGENT      => Config::get('USER_AGENT'),
                CURLOPT_HEADERFUNCTION  => array($this, 'checkHeaders')
            )
        );
    }

    /**
     * CURLOPT_HEADERFUNCTION function to only accept HTML content
     * 
     * @param $ch
     * @param $header
     * @return bool|int
     */
    protected function checkHeaders($ch, $header)
    {
        //Extract header data
        $parts = explode(':', $header);

        //We need a key value pair, so fail early if only one item was found
        if (count($parts) != 2) {
            return strlen($header);
        }

        //Get the key
        $key = $parts[0];

        //Get the value
        $value = trim($parts[1]);

        //We are only looking for the content-type, fail early
        if (strtolower($key) != 'content-type') {
            return strlen($header);
        }

        //Only accept these content types
        $accept_headers = array('text/html', 'application/xhtml+xml');

        //The value can be formatted like 'text/html; charset=iso-8859-1', so we need to parse it.
        //We don't care about the second parameter (charset=iso-8859-1)
        $media_type_data = explode(';', $value);

        //is it acceptable?
        if (in_array(strtolower(trim($media_type_data[0])), $accept_headers)) {
            return strlen($header);
        }

        //Not an acceptable content type, don't download
        return false;
    }

    /**
     * @param $uri
     * @param array $options
     * @throws \SiteMaster\Core\UnexpectedValueException
     * @throws \SiteMaster\Core\HTTPConnectionException
     * @return string - The raw contents of the page
     */
    public function download($uri, $options = array())
    {
        curl_setopt($this->curl, CURLOPT_URL, $uri);
        $result = curl_exec($this->curl);
        
        if (!$result) {
            throw new HTTPConnectionException('Error downloading ' . $uri. $result);
        }

        $effective_url = curl_getinfo($this->curl, CURLINFO_EFFECTIVE_URL);
        if ($effective_url !== $this->page->uri) {
            //Check if it is external
            $registry = new Registry();
            $closest_site = $registry->getClosestSite($effective_url);

            if (($closest_site == false) || ($closest_site->id != $this->site->id)) {
                //The effective URI does not belong to this site.
                throw new UnexpectedValueException('Effective URI does not belong to current site');
            }
            
            //check if it has a fragment
            $effective_url_no_fragment = preg_replace('/#(.*)/', '',$effective_url, -1, $count);
            if ($count) {
                sleep(1); //Prevent flooding of the server
                return $this->download($effective_url_no_fragment, $options);
            }
            
            //Check if this page already exists for this scan.
            if (Page::getByScanIDAndURI($this->scan->id, $effective_url)) {
                throw new UnexpectedValueException('This effective URI was already found.');
            }
            
            //update the page.
            $this->page->uri = $effective_url;
            $this->page->uri_hash = md5($effective_url, true);
            $this->page->save();
        }
        
        return $result;
    }

    /**
     * close the curl object
     */
    public function __destruct()
    {
        curl_close($this->curl);
    }
}
