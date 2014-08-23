<?php
namespace Timber\Forms\Element;

use \DOMElement;

abstract class Element extends \Phalcon\Forms\Element
{
    public $ns   = 'urn:Timber:Form';
    public $type = 'text';
    public $id   = null;
    public $name = null;


 

    public function render($attributes=null)
    {
        return parent::render($attributes);
    }

    /**
     *
     */
    public function getElement($attributes = [])
    {
        $attr = [
            'name'  => sprintf('%s[%s]', $this->_form->formName, $this->_name),
            'id'    => sprintf('%s-%s', $this->_form->formName, $this->_name),
            'xmlns' => $this->ns,
            'value' => parent::getValue(),
            'type'  => $this->type
        ];

        $el = '<input ';

        $attributes = array_merge($attr, $attributes);

        foreach ($attributes as $key => $val) {
            if (!empty($val)) {
                $el .= sprintf('%s="%s" ', $key, $val);
            }
        }
        $el .= " />";
        return $el;
    }

    public function label()
    {
        $id = sprintf('%s-%s', $this->_form->formName, $this->_name);
        $text = parent::getLabel();
    return sprintf('<label for="%s" xmlns="%s">%s</label>',$id, $this->ns, $text);
    }

}
