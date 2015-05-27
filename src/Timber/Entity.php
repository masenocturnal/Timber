<?php
namespace Timber;
use Timber\XML\DOMDocument;
use Timber\EntityInterface;
use Timber\Utils\NSTools;

class Entity implements \JsonSerializable, EntityInterface
{
    public $content;
    public $ns         = 'urn:Timber:Entity';
    public $name       = 'Entity';
    public $clarkNS    = null;
    protected $_format = [];
    
    use \Timber\Utils\Array2XMLTrait;
    use \Timber\Utils\Object2XMLTrait;
    
    public function __construct($content = null)
    {
        $this->content = $content;
        $className  = get_class($this);
        $this->name = NSTools::extractClassname($className);
        $this->ns   = 'urn:'.NSTools::extractNS($className);
        $this->clarkNS = '{'.$this->ns.'}';
    }
    
    
    public function getNS()
    {
        return $this->ns;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    /**
     *
     *
     */
    public function __toString()
    {
        if (is_string($this->content)) {
            return $this->content;
        }
        return var_export($this->content);
    }

    /**
     *
     *
     */
    public function __toXML()
    {
        $writer = new \Sabre\Xml\Writer();
        $writer->openMemory();
//         $writer->namespaceMap = [
//             $this->ns => 'mod',
//         ];
//         
        
        $writer->startElement($this->clarkNS.$this->name);
        
        if (is_string($this->content)) {
            $writer->startElement($this->clarkNS.'string');
            $writer->text($this->content);
            
        } else if(is_array($this->content)) {
            $this->array2XML($writer, $this->content);
        }
        
        $writer->endElement();
        return $writer->outputMemory();

    }
    
    /**
     * Alloww object to be serialized to JSON
     */
    public function jsonSerialize()
    {
        return $this->content;
    }
} 
