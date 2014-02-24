<?php
namespace SiteMaster\Core\Events\Theme;

use SiteMaster\Core\ViewableInterface;
use Symfony\Component\EventDispatcher\Event;

class PrependOutput extends Event
{
    const EVENT_NAME = 'themes.prepend.output';

    protected $prepend = array();
    protected $object = false;
    protected $format = 'html';
    
    public function __construct(ViewableInterface $object, $format = 'html')
    {
        $this->object = $object;
        $this->format = $format;
    }

    /**
     * Get the object that we are altering the output for
     * 
     * @return bool|ViewableInterface
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Get the format of the output
     * 
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Get the items to prepend
     * 
     * @return array the array of items to prepend
     */
    public function getPrepend()
    {
        return $this->prepend;
    }

    /**
     * Prepend a renderable item
     * 
     * @param mixed $renderable The savvy rederable item to prepend to the output
     */
    public function prependOutput($renderable)
    {
        $this->prepend[] = $renderable;
    }
}