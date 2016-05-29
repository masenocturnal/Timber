<?php
namespace Timber\View;

/**
 *
 */
class HTMLHelper 
{
    public static function URL($name, $params = [])
    {
        $params = func_get_args();
        $name = strval($name);
        $default = \Phalcon\Di::getDefault();
        
        if ($params == null) {
            $params = ['for' => $name];
        } else {
            $params['for'] = $name;
        }
        
        if (isset($default['url'])) {
            return $default['url']->get($params);
        }
        return 'invalid';
    }
}