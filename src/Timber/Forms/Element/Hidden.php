<?php
namespace Timber\Forms\Element;


class Hidden extends \Timber\Forms\Element\Element
{
    public $type = 'hidden';


    /**
     *
     */
    public function getElement($attributes = [])
    {
        // @todo use xmlwriter
        $attr = [
            'xmlns' => $this->ns,
            'value' => parent::getValue(),
            'type'  => $this->type
        ];
        
        $attributes = array_merge($attr, $this->getAttributes(), $attributes);

        $attributes['name'] = sprintf('%s[%s]', $this->_form->formName, $this->_name); 
        $attributes['id']   = sprintf('%s-%s', $this->_form->formName, $this->_name);

        $el = '<input ';
     

        foreach ($attributes as $key => $val) {
            if (!empty($val)) {
                $el .= sprintf('%s="%s" ', $key, $val);
            }
        }
        $el .= " />";
        return $el;
    }    
}
