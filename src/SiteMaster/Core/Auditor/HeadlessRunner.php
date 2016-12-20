<?php
namespace SiteMaster\Core\Auditor;

use Monolog\Logger;
use SiteMaster\Core\Config;
use SiteMaster\Core\Plugin\PluginManager;
use SiteMaster\Core\Util;

class HeadlessRunner
{
    public function __construct()
    {
        
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
            $this->generateCompliedScript();
        }
        
        $command = Config::get('PATH_HEADLESS') . ' ' . $this->getCompiledScriptLocation() . ' ' . escapeshellarg($url);
        
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
            Util::log(Logger::ERROR, 'Error parsing headless script', array(
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
    protected function generateCompliedScript()
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
            return Util::getRootDir() . '/tmp/sitemaster_headless_complied_test.js';
        }
        
        return Util::getRootDir() . '/tmp/sitemaster_headless_complied.js';
    }

    /**
     * Delete the complied headless script
     */
    public function deleteCompliedScript()
    {
        @unlink($this->getCompiledScriptLocation());
    }
}
