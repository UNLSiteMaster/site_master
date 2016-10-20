<?php
namespace SiteMaster\Core\Auditor;

use SiteMaster\Core\Config;
use SiteMaster\Core\Util;

class PhantomjsRunner
{
    public function __construct()
    {
        
    }

    /**
     * Run phantomjs script
     * 
     * @param $url
     * @return array|mixed|string
     */
    public function run($url)
    {
        $script = $this->getCompiledScriptLocation();
        
        if (!file_exists($script)) {
            //we need to generate the script
            $this->generateCompliedScript();
        }
        
        $command = Config::get('PATH_PHANTOMJS') . ' ' . $this->getCompiledScriptLocation() . ' ' . escapeshellarg($url);
        
        $result = shell_exec($command);
        
        if (!$result) {
            return [];
        }
        
        $result = json_decode($result, true);
        
        return $result;
    }

    /**
     * Compile (or recompile) the the phantomjs script
     * 
     * @return string
     */
    protected function generateCompliedScript()
    {
        $command = Config::get('PATH_PHP') . ' ' . Util::getRootDir() . '/scripts/compilePhantomjsScript.js.php';
        return shell_exec($command);
    }

    /**
     * Get the compiled phantomjs script location
     * 
     * @return string
     */
    public function getCompiledScriptLocation()
    {
        return Util::getRootDir() . '/scripts/sitemaster_phantom_complied.js';
    }

    /**
     * Delete the complied phantomjs script
     */
    public function deleteCompliedScript()
    {
        unlink($this->getCompiledScriptLocation());
    }
}
