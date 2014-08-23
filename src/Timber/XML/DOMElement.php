<?php
namespace Timber\XML;

/**
 *
 *
 */
class DOMElement extends \DOMElement
{
    public function xpath($query)
    {
        return $this->ownerDocument->getXpathProcessor()->query($query,$this);
    }

    public function createElement($name, $value = null, $append = false)
    {
        // set the namespace to that of the current element
        $this->ownerDocument->currentNamespace = $this->namespaceURI;

        $el = $this->ownerDocument->createElement($name,$value);

        // automatically append to the current node
        if ($append == true) {
            return $this->appendChild($el);
        }

        return $el;
    }

    /**
     * Set element attributes all in one key value pair array
     *
     * @param array $attrs Key/Val of the attributes to set
     */
    public function setAttributes(array $attrs)
    {
        foreach ($attrs as $k => $v) {
            $this->setAttribute($k, $v);
        }
        return $this;
    }

    public function addAsElements(array $data , $list = null, $excludeList = false)
    {
        $this->ownerDocument->currentNamespace = $this->namespaceURI;

        return $this->ownerDocument->addAsElements($this, $data, $list, $excludeList);
    }

    public function get($query, array $params = null )
    {
        return $this->ownerDocument->getNode($query, $this, $params);
    }
}
