<?php
namespace Timber;
use Timber\XML\DOMDocument;
use Timber\EntityInterface;
use Timber\Utils\NSTools;

class EntityCollection extends Entity
{
    /**
     *
     *
     */
    public function __toXML()
    {

        // remove the Collection part of FooCollection
        $entityName = substr($this->name, 0, -10); 
        $writer = new \Sabre\Xml\Writer();
        $writer->openMemory();
        /*
         * Disabled this as it's a bit difficult to determine
         * which prefix is use .. this causes it to create a dynamic
         * prefix
        $writer->namespaceMap = [
            $this->ns => 'mod', 
        ];
        */
        $clarkNS = '{'.$this->ns.'}';
        $writer->startElement($clarkNS.$this->name);
        $writer->startAttribute('xmlns');
        $writer->text($this->ns);
        $writer->endAttribute();
        
        foreach($this->content as $key => $val) {
            $writer->startElement($entityName);
            $writer->write($val);
            $writer->endElement();
        }

        $writer->endElement();
        return $writer->outputMemory();
    }
} 
