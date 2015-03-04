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
    protected $_format = [];
    
    use \Timber\Utils\Array2XMLTrait;
    use \Timber\Utils\Object2XMLTrait;
    
    public function __construct($content = null)
    {
        $this->content = $content;
        $className  = get_class($this);
        $this->name = NSTools::extractClassname($className);
        $this->ns   = 'urn:'.NSTools::extractNS($className);
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
        $dom = new DOMDocument();
        $el = $dom->appendChild($dom->createElementNS($this->ns,$this->name));
        
        if (is_string($this->content)) {
            $dom->appendChild($dom->createElementNS($this->ns,'string', $this->content));
        } elseif(is_array($this->content)) {
            $this->Array2XML($el, $this->content);
        } else if (is_object($this->content)) {
            $this->object2XML($el, $this->content);
        }

        return $dom->saveXML($dom->documentElement);
    }
    
    /**
     * Alloww object to be serialized to JSON
     */
    public function jsonSerialize()
    {
        return $this->content;
    }
} 
