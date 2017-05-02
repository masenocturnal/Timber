<?php
namespace Timber;
use Timber\XML\DOMDocument;
use Timber\EntityInterface;
use Timber\Utils\NSTools;
use Sabre\Xml\Writer as Writer;
use Sabre\Xml\XmlSerializable;
use \ArrayIterator;
use \ArrayAccess;
use \IteratorAggregate;
use \InvalidArgumentException;


/**
 * @todo now that it extends ArrayObject we should move * content to use the inbuilt array, using traversal
 * methods etc..
 */
abstract class EntityCollection implements EntityInterface, ArrayAccess, IteratorAggregate, XmlSerializable
{
    public $content;
    public $ns         = 'urn:Timber:EntityCollection';
    public $name       = 'EntityCollection';
    public $entity     = 'Timber\Entity';
    public $clarkNS    = null;

    protected $_format = [];
    private  $_log     = null;

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
        $this->xmlSerialize($writer);
        $writer->endElement();
        return $writer->outputMemory();
    }

    function xmlSerialize(Writer $writer)
    {
        foreach ($this->content as $k => $v) {
            if ($v instanceof Entity){
                $className = get_class($v);
                $v->name = NSTools::extractClassname($className);
                $v->ns = 'urn:'.NSTools::extractNS($className);
                $writer->writeRaw($v->__toXML());
            } else if(is_array($v)) {
                $writer->startElementNS(null, $this->name, $this->ns);
                $tmpEntity = new $this->entity($v);
                $writer->writeRaw($tmpEntity->__toXML());
                $writer->endElement();
            }
        }
    }


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
