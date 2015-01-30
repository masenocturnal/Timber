<?php
namespace Timber\Utils;

class NSTools
{
    /**
     * Extract a Namespace from the class
     */
    public static function extractNS($className)
    {
        // by default don't go any more than 2 layers deep
        // if you want to go further you need to make an actual class
        // which states the namespace
        
        // strip '\' if it's the first char 
        $className = ltrim($className, '\\');
        
        // quick way to get Module\Foo
        $nsArr = explode('\\', $className, 3);

        if (isset($nsArr[2])) {
            unset($nsArr[2]);
        }
        return implode(':', $nsArr);
    }
    
    /**
     * Extract the local name of the class from a fully namespaced 
     * class name
     */
    public static function extractClassname($className)
    {
        $pos = strripos($className, '\\', -1);
        $className = substr($className, $pos+1);
        return ucfirst($className);
    }
    
}

