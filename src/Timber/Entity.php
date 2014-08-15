<?php
namespace Timber;
use Timber\XML\DOMDocument;

class Entity implements \JsonSerializable
{
    public $content;
    public $ns = 'urn:Timber:Entity:Foo';
    
    public function __construct($content = null)
    {
        $this->content = $content;
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
    }

    /**
     *
     *
     */
    public function __toXML()
    {
        $dom = new DOMDocument();
        if (is_string($this->content)) {
            $dom->appendChild($dom->createElementNS($this->ns,'string', $this->content));
        } elseif(is_array($this->content)) {
            // @todo array to XML
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