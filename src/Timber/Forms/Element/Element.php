<?php
namespace Timber\Forms\Element;

use \DOMElement;
use Phalcon\Validation\ValidatorInterface;

abstract class Element extends \Phalcon\Forms\Element
{
    public $ns   = 'urn:Timber:Form';
    public $type = 'text';
    public $id   = null;
    public $name = null;
    public $constraintNames = [];


    public function addValidator(ValidatorInterface $validator)
    {
        $className = get_class($validator);
        $this->constraintNames[] = substr($className, 1+strrpos($className, '\\'));
        parent::addValidator($validator);
        return $this;
    }

    /**
     *
     */
    public function render($attributes=null)
    {
        return parent::render($attributes);
    }

    /**
     *
     */
    public function getElement($attributes = [])
    {
        // @todo use xmlwriter
        $attr = [
            'name'  => sprintf('%s[%s]', $this->_form->formName, $this->_name),
            'id'    => sprintf('%s-%s', $this->_form->formName, $this->_name),
            'xmlns' => $this->ns,
            'value' => parent::getValue(),
            'type'  => $this->type
        ];

        $el = '<input ';
        $attributes = array_merge($attr, $this->getAttributes(), $attributes);

        foreach ($attributes as $key => $val) {
            if (!empty($val)) {
                $el .= sprintf('%s="%s" ', $key, $val);
            }
        }
        $el .= " />";
        return $el;
    }
    // @todo use xmlwriter
    public function label()
    {
        $id = sprintf('%s-%s', $this->_form->formName, $this->_name);
        $text = parent::getLabel();
        $class = null;
        if (in_array('PresenceOf', $this->constraintNames)) {
            $class = 'required';
        }
        return sprintf('<label for="%s"  class="%s" xmlns="%s">%s</label>',$id, $class, $this->ns, $text);
    }

}
