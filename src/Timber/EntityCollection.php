<?php
namespace Timber;
use Timber\XML\DOMDocument;
use Timber\EntityInterface;
use Timber\Utils\NSTools;
use Sabre\Xml\XmlSerializable;
use Sabre\Xml\Writer as XmlWriter;
use \Traversable;

class EntityCollection implements EntityInterface
{
    public $content;
    public $ns         = 'urn:Timber:EntityCollection';
    public $name       = 'EntityCollection';
    public $entity     = 'Timber\Entity';
    public $clarkNS    = null;
    protected $_format = [];

    public function __construct($content = null)
    {
        $className     = get_class($this);
        $this->content = $content;
        $this->entity  = substr($className, 0, -10);
        $this->name    = NSTools::extractClassname($className);
        $this->ns      = 'urn:'.NSTools::extractNS($className);
        $this->clarkNS = '{'.$this->ns.'}';
    }

    public function __toXML()
    {
        $writer = new \Sabre\Xml\Writer();
        $writer->openMemory();
        $writer->startElementNS(null, $this->name, $this->ns);

        foreach ($this->content as $k => $v) {
            if ($v instanceof Entity){
                $writer->writeRaw($v->__toXML());
            } else if(is_array($v)) {
                $tmpEntity = new $this->entity($v);
                $writer->writeRaw($tmpEntity->__toXML());
            }
        }
        $writer->endElement();
        return $writer->outputMemory();
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
