<?php
namespace Timber\Utils;

trait Array2XMLTrait {
    public function array2XML($writer, $arr) {
        
        foreach ($arr as $k => $v) {
            if(is_numeric($k)) {
                if(is_array($v)) {
                    $writer->startElement('{'.$this->ns.'}'.$k);
                    $writer->text($v);
                    $writer->endElement();
                }  else {
                    throw new UnexpectedValueException('Unable to serialize XML');
                }
            } else {
                // look for a function named {key}Format
                $name = $k.'Format';
                if (method_exists($this, $name)) {
                    $this->$name($writer, $k, $v);
                } elseif (is_array($v)) {
                    $writer->startElement('{'.$this->ns.'}');
                    $writer->writeRaw($this->array2XML($writer, $v));
                    $writer->endElement();
                } elseif (is_object($v)) {
                    $writer->startElement('{'.$this->ns.'}');
                    $writer->writeRaw($this->object2XML($writer, $v));
                    $writer->endElement();
                } else {
                    $writer->startElement('{'.$this->ns.'}'.$k);
                    $writer->text($v);
                    $writer->endElement();
                }
            } 
        }
    }
}
