<?php
namespace Timber\Model\Resultset;

use \Timber\Entity;
use \Phalcon\Mvc\Model\Resultset\Simple;
use \Timber\EntityInterface;
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
    
    public function __construct(array $columnMap = null, \Phalcon\Mvc\ModelInterface $model, \Phalcon\Db\Result\Pdo $result, $cache = null, $keepSnapshots = null, $entityName = null)
    {
        // set the entity name so we can create
        // the XML representation of it in the correct namespace
        $this->entityName = $entityName;
        
        parent::__construct($columnMap, $model, $result, $cache, $keepSnapshots);
    }

    public function current()
    {
        $pos = strripos($this->entityName, '\\', -1);
        $ns  = substr($this->entityName, strpos('\\', $this->entityName), $pos);
        $className = substr($this->entityName, $pos+1);
       
        if ($this->entityName != null) {
        
            // only create the class if it hasn't been defined yet
            if (!class_exists($this->entityName)) {
             
                $str = "
                    namespace $ns;
                    
                    class $className extends \Timber\Entity implements \Timber\EntityInterface 
                    {
                    
                    }
                ";
                eval($str);
            }
            
            $entity = new $this->entityName(parent::current());
            $entity->ns = 'urn:'.str_replace('\\', ':', $ns);
                
            return $entity;
        }
        return new Entity(parent::current(), $this->entityName);
    }
    
    public function __toXML()
    {
        $doc = new \Timber\XML\DOMDocument();
        
        // @todo move this to a central location
        $pos = strripos($this->entityName, '\\', -1);
        $ns  = substr($this->entityName, strpos('\\', $this->entityName), $pos);
        $className = substr($this->entityName, $pos+1);
       
        $el  = $doc->appendChild($doc->createElementNS('urn:'.str_replace('\\', ':', $ns), $className.'s'));
        
        foreach ($this as $foo) {
            $el->appendXML($foo->__toXML());
            
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