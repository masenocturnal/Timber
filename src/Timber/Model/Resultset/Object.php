<?php
namespace Timber\Model\Resultset;

use Timber\Entity;

class Object extends \Phalcon\Mvc\Model\Resultset\Simple
{

    
    
    public function current()
    {
        echo "FLAH";
        $x =  parent::current();
//         var_dump(get_object_vars($this));
        return new Entity($x);
    }

}