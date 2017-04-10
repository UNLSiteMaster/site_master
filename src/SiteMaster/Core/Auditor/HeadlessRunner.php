<?php
namespace SiteMaster\Core\Auditor;

use Monolog\Logger;
use SiteMaster\Core\Config;
use SiteMaster\Core\Plugin\PluginManager;
use SiteMaster\Core\Util;

class HeadlessRunner
{
    protected $daemon_name;

    /**
     * HeadlessRunner constructor.
     * 
     * This will generate and scope headless js for each daemon instance
     * 
     * @param $daemon_name
     * @throws \Exception
     */
    public function __construct($daemon_name = 'default')
    {
        if (!ctype_alnum($daemon_name)) {
            throw new \Exception('Invalid daemon_name, it must be alphanumeric');
        }
        $this->daemon_name = $daemon_name;
    }

    /**
     * Run a headless script
     * 
     * @param $url
     * @return array|mixed|string
     */
    public function run($url)
    {
        //Do we need to run headless? (do any metrics use it?)
        $pluginManager = PluginManager::getManager();
        
        if (!$pluginManager->headlessTestsExist()) {
            return false;
        }
        
        $script = $this->getCompiledScriptLocation();
        
        if (!file_exists($script)) {
            //we need to generate the script
            $this->generateCompiledScript();
        }
        
        $command = 'timeout ' . escapeshellarg(Config::get('HEADLESS_TIMEOUT')) //Prevent excessively long runs
            . ' ' . Config::get('XVFB_COMMAND')
            . ' ' . Config::get('PATH_NODE')
            . ' ' . $this->getCompiledScriptLocation()
            . ' ' . escapeshellarg($url);
        
        $result = shell_exec($command);
        
        if (!$result) {
            return false;
        }

        //Output MAY contain many lines, but the json response is always on one line.
        $output = explode(PHP_EOL, $result);
        
        $json = false;
        foreach ($output as $line) {
            //Loop over each line and find the json response
            if ($json = json_decode($line, true)) {
                //Found it... return the data.
                break;
            }
        }
        
        if (!$json) {
            //Log the error
            Util::log(Logger::NOTICE, 'Error parsing headless script', array(
                'result' => $result,
            ));
            
            return false;
        }
        
        return $json;
    }

    /**
     * Compile (or recompile) the the headless script
     * 
     * @return string
     */
    protected function generateCompiledScript()
    {
        include Util::getRootDir() . '/data/compileHeadless.js.php';
    }

    /**
     * Get the compiled headless script location
     * 
     * @return string
     */
    public function getCompiledScriptLocation()
    {
        if (Config::get('ENVIRONMENT') == Config::ENVIRONMENT_TESTING) {
            return Util::getRootDir() . '/tmp/sitemaster_headless_'.$this->daemon_name.'_compiled_test.js';
        }
        
        return Util::getRootDir() . '/tmp/sitemaster_headless_'.$this->daemon_name.'_compiled.js';
    }

    /**
     * Delete the compiled headless script
     */
    public function deleteCompiledScript()
    {
        @unlink($this->getCompiledScriptLocation());
    }
}
