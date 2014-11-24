<?php
namespace Timber\Utils;

trait Object2XMLTrait {
    public function object2XML($node, $obj) {        
        $dom = $node->ownerDocument;
        
        foreach (get_object_vars($obj) as $k=>$v) {
            if (is_object($v)) {
                $newNode = $node->appendChild($dom->createElement($this->ns, $k));
                $this->object2XML($newNode, $v);
            } else {
                $node->appendChild($dom->createElementNS($this->ns, $k, $v));                
            }
        }
    }
}