<?php
namespace Timber\Utils;

trait Array2XMLTrait {
    public function array2XML(\DOMNode $node, array $arr) {
        $dom = $node->ownerDocument;
        
        foreach ($arr as $k=>$v) {
            if (is_array($v)) {
                $newNode = $node->appendChild($dom->createElementNS($this->ns, $k));
                $this->array2XML($newNode, $v);
            } else {
                $node->appendChild($dom->createElementNS($this->ns, $k, $v));
            }
        }
    }
}
