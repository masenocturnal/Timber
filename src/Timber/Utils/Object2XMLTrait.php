<?php
namespace Timber\Utils;

use Timber\EntityInterface;
use Sabre\Xml\XmlSerializable;

trait Object2XMLTrait {
    public function object2XML(&$writer, &$obj)
    {
        if ($obj instanceof XmlSerializable){
            $obj->xmlSerialize($writer);
        } else {
            foreach (get_object_vars($obj) as $k => $v) {
                if ($this->_format != null && isset($this->_format[$k])) {
                    $this->_format[$k]($writer, $k, $v);
                } elseif (is_object($v)) {
                    $this->object2XML($writer, $v);
                } elseif (is_string($v)) {
                    $writer->startElement($k);
                    $writer->text($v);
                    $writer->endElement();
                } elseif (is_array($v)) {
                    $this->array2XML($writer, $v);
                } else {
                    $writer->startElement($k);
                    $writer->text($v);
                    $writer->endElement();
                }
            }
        }
    }
}
