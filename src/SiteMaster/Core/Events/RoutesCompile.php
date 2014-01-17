<?php
namespace SiteMaster\Core\Events;

use Symfony\Component\EventDispatcher\Event;

class RoutesCompile extends Event
{
    const EVENT_NAME = 'routes.compile';

    protected $routes = array();

    public function __construct($routes)
    {
        $this->routes = $routes;
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function addRoute($regex, $class)
    {
        $this->routes[$regex] = $class;
    }
}