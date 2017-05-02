<?php
namespace Timber\Model;

use \PDO;
use Phalcon\Mvc\Model\Resultset\Simple as ResultSet;
use \Timber\EntityInterface;


/**
 *
 *
 */
abstract class Model extends \Phalcon\Mvc\Model
{
    protected $_log = null;

    public static function findFirst($parameters = NULL)
    {
        if (!isset($parameters['hydration'])) {
            $parameters['limit']     = 1;
            $parameters['hydration'] = Resultset::HYDRATE_ARRAYS;
        }
        
        $entity = self::find($parameters);

        $entity = $entity->getFirst();
        
        return $entity;
    }

    public function getSource()
    {
        if ($this->tableName != null) {
            return $this->tableName;
        }
        return parent::getSource();
    }

    public function setLogger($logger)
    {
        $this->_log = $logger;
    }
}
