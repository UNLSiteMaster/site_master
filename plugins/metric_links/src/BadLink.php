<?php
namespace SiteMaster\Plugins\Metric_links;

class BadLink
{
    protected $uri;
    protected $http_code;
    protected $connection_error;
    protected $curl_code;
    protected $curl_message;
    
    public function __construct($uri, $http_code, $connection_error = false, $curl_code = 0, $curl_message = '')
    {
        $this->uri = $uri;
        $this->http_code = $http_code;
        $this->connection_error = $connection_error;
        $this->curl_code = $curl_code;
        $this->curl_message = $curl_message;
    }
    
    public function getURI()
    {
        return $this->uri;
    }
    
    public function getHTTPCode()
    {
        return $this->http_code;
    }
    
    public function getCURLCode()
    {
        return $this->curl_code;
    }
    
    public function getMachineName()
    {
        $machine_name = 'link_';
        if ($this->connection_error) {
            $machine_name .= 'connection_error_' . $this->curl_code;
        } else {
            $machine_name .= 'http_' . $this->http_code;
        }
        
        return $machine_name;
    }
}