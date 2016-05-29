<?php
namespace Timber\Forms\Element;


class Select extends \Phalcon\Forms\Element\Select
{
    public $ns   = 'urn:Timber:Form';
    public $type = 'text';
    public $id   = null;
    public $name = null;
    public $constraintNames = [];


    public function getElement($attributes = [])
    {

        // @todo use xmlwriter
        $attr = [
            'name'  => sprintf('%s[%s]', $this->_form->formName, $this->_name),
            'id'    => sprintf('%s-%s', $this->_form->formName, $this->_name),
            'xmlns' => $this->ns,
            'type'  => $this->type
        ];

        $el = '<select ';
        
        $attributes = array_merge($attr, $this->getAttributes(), $attributes);


        foreach ($attributes as $key => $val) {
            // the !is_array is a bit of a hack
            // for some reason the "using" directive is present in 
            // the attributes...should we not be using the 
            // getAttributes() method ?
            if (!empty($val) && !is_array($val)) {                
                $el .= sprintf('%s="%s" ', $key, $val);
            }
        }
        
        $el .= " >";

        $selected = parent::getValue();

        if ($this->_optionsValues){
            $el .= '<option value="">Choose Option</option>';
            foreach ($this->_optionsValues as $key => $val) {
                $value = null;
                
                if (isset($this->_attributes['using'])) {
                    $key  = $val[$this->_attributes['using'][0]];
                    $value = $val[$this->_attributes['using'][1]];
                } else {
                    $value = $val;
                }
                $current  = ($key == $selected)? 'selected':'';
                $el .= '<option value="'.$key.'" selected="'.$current.'">'.$value.'</option>';

            }
        }
        $el .= "</select >";
        return $el;
    }
}
