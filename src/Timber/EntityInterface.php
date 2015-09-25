<?php
namespace Timber;

interface EntityInterface
{
    
    public function getName();
    public function getNS(); 
    public function __toXML();
    public function setLogger($logger);
    //public function object2XML(&$node, &$obj);
   // public function array2XML(\DOMNode $node, $arr);

}