<?php
namespace Timber;

use Timber\XML\DOMDocument;
use Timber\EntityInterface;
use Timber\Utils\NSTools;
use ArrayAccess;
use JsonSerializable;

class Entity implements ArrayAccess, JsonSerializable, EntityInterface
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

    public function offsetExists($offset)
    {
        if ($this->content != null)
        {
            if (is_array($this->content)) {
                return isset($this->content[$offset]);
            } elseif (is_object($this->content)) {
                return isset($this->content->$offset);
            } else {
                throw new InvalidArgumentException('Internal storage is not array or object addressable');
            }
        }
    }

    public function offsetGet($offset)
    {
        if ($this->content != null)
        {
            if (is_array($this->content)) {
                return $this->content[$offset];
            } elseif (is_object($this->content)) {
                return $this->content->$offset;
            } else {
                throw new InvalidArgumentException('Internal storage is not array or object addressable');
            }
        }
    }
    
    public function offsetSet($offset, $value)
    {
        if ($this->content != null)
        {
            if (is_array($this->content)) {
                $this->content[$offset] = $value;
            } elseif (is_object($this->content)) {
                $this->content->$offset = $value;
            } else {
                throw new InvalidArgumentException('Internal storage is not array or object addressable');
            }
        }
    }

    public function offsetUnset($offset)
    {
        if (isset($this->content[$offset])) {
            unset($this->content[$offset]);
        }
    }

    /**
     *
     *
     */
    public function __toString()
    {
        if ($this->content != null) {
            return '';
        }

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

        $writer->startElement($this->name);
        $this->writeNS($writer, $this->ns);
        
        if (is_string($this->content)) {
            $writer->text($this->content);

        } else if(is_array($this->content)) {
            $this->array2XML($writer, $this->content);
        } elseif (is_object($this->content)) {
            $this->object2XML($writer, $this->content);
        }
        
        $writer->endElement();
        return $writer->outputMemory();

    }

    public function __set($offset, $value)
    {
        if ($this->content != null)
        {
            if (is_array($this->content)) {
                $this->content[$offset] = $value;
            } else if(is_object($this->content)) {
                $this->content->$offset = $value;
            } else {
                throw new \ErrorException('Unable to set attribute. $this->content is not an object:  '.$offset);
            }
        }
        return false;
    }

    public function __get($offset)
    {
        if ($this->content != null)
        {
            if (is_array($this->content) && isset($this->content[$offset]))
            {
                return $this->content[$offset];
            } else if (is_object($this->content) && isset($this->content->$offset)) {
                return $this->content->$offset;
            }

        }
        return false;
    }

    /**
     * Alloww object to be serialized to JSON
     */
    public function jsonSerialize()
    {
        return $this->content;
    }
} 
