<?php
namespace Timber\Model\Resultset;

use Timber\Entity;
use Phalcon\Mvc\Model\Resultset\Simple;
use Timber\EntityInterface;
use Timber\Utils\NSTools;

/**
 * Extension of the Phalcon result set that accepts an optional argument 
 *
 */
class Object extends Simple implements EntityInterface
{
    public $entityName = null;
    
    public $baseClass  = '\Timber\Entity';
    public $ns         = 'urn:Timber:ResultSet';
    
    // trait to turn object into XML
    use \Timber\Utils\Object2XMLTrait;
    use \Timber\Utils\Array2XMLTrait;
    
    public function __construct(array $columnMap = null, \Phalcon\Mvc\ModelInterface $model, \Phalcon\Db\Result\Pdo $result, $cache = null, $keepSnapshots = null, $entityClass = null)
    {
        // set the entity name so we can create
        // the XML representation of it in the correct namespace
        $this->entityName = $entityClass;
        
        parent::__construct($columnMap, $model, $result, $cache, $keepSnapshots);
    }

    public function current()
    {
        $ns  = NSTools::extractNS($this->entityName, '\\');
        $className = NSTools::extractClassname($this->entityName);
        
        if ($this->entityName != null) {
        
            // only create the class if it hasn't been defined yet
            // try and autoload it if it doesn't
            if (!class_exists($this->entityName, true)) {
               
                $str = "
                    namespace $ns;
                    
                    class ".$className." extends \Timber\Entity implements \Timber\EntityInterface 
                    {
                    
                    }
                ";
                eval($str);
            }
            
            $entity = new $this->entityName(parent::current());
            
            return $entity;
        }
        
        return parent::current();
        return new Entity(parent::current(), $this->entityName);
    }
    
    public function __toXML()
    {
        $doc = new \Timber\XML\DOMDocument();
        
        $ns        = NSTools::extractNS($this->entityName);
        $className = NSTools::extractClassname($this->entityName);
       
        $el = $doc->appendChild($doc->createElementNS('urn:'.$ns, $className.'Collection'));
        
        foreach ($this as $row) {
            $el->appendXML($row->__toXML());
        }
        
        return $doc->saveXML($doc->documentElement);
    }
    
    public function getName()
    {
        return $this->entityName;
    }
    
    public function getNS() 
    {
        return $this->ns;
    }
}
