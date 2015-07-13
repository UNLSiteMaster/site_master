<?php
namespace SiteMaster\Core;

use RegExpRouter\Router;
use SiteMaster\Core\Events\RoutesCompile;
use SiteMaster\Core\Plugin\PluginManager;
use SiteMaster\Core\User\Session;

class Controller
{
    public $output = null;

    public $options = array(
        'model'  => false,
        'format' => 'html'
    );

    public function __construct($options = array())
    {
        $this->options = $options + $this->options;
        $this->options['current_url'] = Util::getCurrentURL();

        $this->route();
        
        $this->run();
    }

    public function getPluginRoutes()
    {
        $event = PluginManager::getManager()->dispatchEvent('routes.compile', new RoutesCompile(array()));

        return $event->getRoutes();
    }

    public function route()
    {
        $options = array(
            'baseURL' => Config::get('URL'),
            'srcDir'  => dirname(__FILE__) . "/",
        );

        $router = new Router($options);
        $router->setRoutes($this->getPluginRoutes());

        // Initialize App, and construct everything
        $this->options = $router->route($_SERVER['REQUEST_URI'], $this->options);
    }

    /**
     * Populate the actionable items according to the view map.
     *
     * @throws Exception if view is unregistered
     */
    public function run()
    {
        try {
            $this->verifyModel();
            
            if ($this->options['format'] == 'partial') {
                \Savvy_ClassToTemplateMapper::$output_template[__CLASS__] = 'SiteMaster/Core/Controller-partial';
            }

            $this->output = new $this->options['model']($this->options);
            
            if (!$this->output instanceof ViewableInterface) {
                throw new RuntimeException("All Output must be an instance of \\SiteMaster\\Core\\ViewableInterface");
            }

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $this->handlePost($this->output);
            }
        } catch (\Exception $exception) {
            if (get_class($exception) != 'ViewableInterface') {
                $e = new ViewableException($exception->getMessage(), $exception->getCode(), $exception);
            } else {
                $e = $exception;
            }
            $this->output = $e;
        }
    }

    /**
     * Verify that a model has been requested
     * 
     * @return bool
     * @throws RuntimeException
     */
    public function verifyModel()
    {
        if (!isset($this->options['model'])
            || false === $this->options['model']) {
            throw new RuntimeException('Un-registered view', 404);
        }
        
        return true;
    }

    public function handlePost($object)
    {
        if (!$object instanceof PostHandlerInterface) {
            throw new RuntimeException("All Post Handlers must be an instance of \\SiteMaster\\Core\\PostHandlerInterface");
        }

        $result = $object->handlePost($this->options, $_POST, $_FILES);
        
        if (!$result && $object instanceof AbstractPostHandler) {
            $object->sendErrorMessage();
        }
        
        return $result;
    }

    public function getFlashBagMessages()
    {
        return Session::getSession()->getFlashBag()->all();
    }

    public static function addValidationMessage(ValidationMessage $message)
    {
        $session = Session::getSession();
        $session->getFlashBag()->add('alert', $message);
    }

    public static function addFlashBagMessage(FlashBagMessage $message)
    {
        $session = Session::getSession();
        $session->getFlashBag()->add('alert', $message);
    }

    public static function redirect($url, FlashBagMessage $message = NULL, $exit = true)
    {
        if ($message) {
            self::addFlashBagMessage($message);
        }

        header('Location: '.$url);
        if (!defined('CLI')
            && false !== $exit) {
            exit($exit);
        }
    }
}