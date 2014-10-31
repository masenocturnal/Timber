<?php
namespace Timber\Utils;

use Timber\XML\DOMElement;

trait Array2XMLTrait {
    public function array2XML(DOMElement $node, array $arr) {
        foreach ($arr as $k=>$v) {
            if (is_array($v)) {
                $newNode = $node->appendChild($node->createElement($k));
                $this->array2XML($newNode, $v);
            } else {
                $node->appendChild($node->createElement($k, $v));
            }
        }
    }
}
