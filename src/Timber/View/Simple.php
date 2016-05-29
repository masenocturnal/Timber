<?php
namespace Timber\View;

/**
 *
 */
class Simple extends \Phalcon\Mvc\View implements \Phalcon\Mvc\ViewInterface
{
    public function setVar($key, $val)
    {
        return parent::setVar($key, $val);
    }
    
    public function setContent($content)
    {
        return parent::setContent($content);

    }
    
    public function render($controllerName, $actionName, $params=null)
    {
        return parent::render($controllerName, $actionName, $params);
    }
    
    
}