<?php
namespace Timber;
use Timber\XML\DOMDocument;
use Timber\EntityInterface;
use Timber\Utils\NSTools;
use Sabre\Xml\XmlSerializable;
use Sabre\Xml\Writer as XmlWriter;
use \ArrayIterator;
use \ArrayAccess;
use \IteratorAggregate;
use \InvalidArgumentException;

/**
 * @todo now that it extends ArrayObject we should move * content to use the inbuilt array, using traversal
 * methods etc..
 */
abstract class EntityCollection implements EntityInterface, ArrayAccess, IteratorAggregate
{
    public $content;
    public $ns         = 'urn:Timber:EntityCollection';
    public $name       = 'EntityCollection';
    public $entity     = 'Timber\Entity';
    public $clarkNS    = null;
    protected $_format = [];

    public function __construct($content = [])
    {
        if ($content === null)
        {
            throw new InvalidArgumentException('You must pass a non null value');
        }

        $className     = get_class($this);

        $this->content = $content;
        $this->entity  = substr($className, 0, -10);
        $this->name    = NSTools::extractClassname($className);
        $this->ns      = 'urn:'.NSTools::extractNS($className);
        $this->clarkNS = '{'.$this->ns.'}';

        $this->content = $content;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->content);
    }

    public function offsetSet($offset, $val)
    {
        $this->content[$offset] = $val;
    }

    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            unset($this->content[$offset]);
        }
    }
    
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->content[$offset];
        }
        return null;
    }
    
    public function offsetExists($offset)
    {
        return isset($this->content[$offset]);
    }

    public function __toXML()
    {

        $writer = new \Sabre\Xml\Writer();
        $writer->openMemory();
        $writer->startElementNS(null, $this->getName(), $this->ns);

        foreach ($this->content as $k => $v) {
            if ($v instanceof Entity){
                $className = get_class($v);
                $v->name = NSTools::extractClassname($className);
                $v->ns = 'urn:'.NSTools::extractNS($className);
                $writer->writeRaw($v->__toXML());
            } else if(is_array($v)) {
                $tmpEntity = new $this->entity($v);
                $writer->writeRaw($tmpEntity->__toXML());
            }
        }

        $writer->endElement();
        return $writer->outputMemory();
    }


//     public function offsetExists($offset)
//     {
//         if ($this->content != null)
//         {
//             if (is_array($this->content)) {
//                 return isset($this->content[$offset]);
//             } elseif (is_object($this->content)) {
//                 return isset($this->content->$offset);
//             } else {
//                 throw new InvalidArgumentException('Internal storage is not array or object addressable');
//             }
//         }
//     }
//
//     public function offsetGet($offset)
//     {
//         if ($this->content != null)
//         {
//             if (is_array($this->content)) {
//                 return $this->content[$offset];
//             } elseif (is_object($this->content)) {
//                 return $this->content->$offset;
//             } else {
//                 throw new InvalidArgumentException('Internal storage is not array or object addressable');
//             }
//         }
//     }
//
//     public function offsetSet($offset, $value)
//     {
//         if ($this->content != null)
//         {
//             if (is_array($this->content)) {
//                 $this->content[$offset] = $value;
//             } elseif (is_object($this->content)) {
//                 $this->content->$offset = $value;
//             } else {
//                 throw new InvalidArgumentException('Internal storage is not array or object addressable');
//             }
//         }
//     }
//

    public function log($message)
    {
        if ($this->_log != null) {
            $this->_log->debug($message);
        }
    }

    public function setLogger($logger)
    {
        $this->_log = $logger;
    }

    public function getNS()
    {
        return $this->ns;
    }

    public function getName()
    {
        return $this->name;
    }
}
