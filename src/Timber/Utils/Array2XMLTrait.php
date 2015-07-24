<?php
namespace Timber\Utils;

trait Array2XMLTrait {

    protected function writeNS($writer, $ns=null)
    {
        if ($ns != null){
            $writer->startAttribute('xmlns');
            $writer->text($ns);
            $writer->endAttribute();
        }
    }

    public function array2XML($writer, $arr, $ns = null) {

        foreach ($arr as $k => $v) {

            if(is_numeric($k)) {
                if(is_array($v)) {
                    $writer->startElement($k);
                    $this->writeNS($writer, $ns);
                    $writer->text($v);
                    $writer->endElement();
                }  else {
                    $writer->startElement('item');
                    $this->writeNS($writer, $ns);
                    $writer->startAttribute('position');
                    $writer->text($k);
                    $writer->endAttribute();
                    $writer->text($v);
                    $writer->endElement();
                }
            } else {
                // look for a function named {key}Format
                $name = $k.'Format';
                if (method_exists($this, $name)) {
                    $this->$name($writer, $k, $v);
                } elseif (is_array($v)) {
                    $writer->startElement($k);
                    $this->writeNS($writer, $ns);
                    $writer->writeRaw($this->array2XML($writer, $v));
                    $writer->endElement();
                } elseif (is_object($v)) {
                    $this->object2XML($writer, $v, $ns);
                } else {
                    $writer->startElement($k);
                    $this->writeNS($writer, $ns);
                    $writer->text($v);
                    $writer->endElement();
                }
            }
        }
    }
}
