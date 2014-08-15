<?php
namespace Timber\Forms;

abstract class Form extends \Phalcon\Forms\Form
{
    public $formName = null;
    public $formNs   = 'urn:Timber:Form';
    public $method   = 'post';
    public $valid    = null;

    /**
     * @override Automatically add set the form
     * when adding the element to the form
     */
    public function add($element, $pos = NULL, $type = NULL)
    {
        $element->setForm($this);
        parent::add($element, $pos, $type);
    }

    /**
     * @override isValid
     *
     * Overridden so that we can check the form name
     *
     */
    public function isValid($data = null, $entity = null)
    {
        if (null != $this->formName) {
            if (isset($data[$this->formName])) {
                $data = $data[$this->formName];
            }
        }

        $this->valid = parent::isValid($data, $entity);
        return $this->valid;
    }

    /**
     * Render as XML
     *
     */
    public function __toXML()
    {
        $dom    = new \DOMDocument();

        // get the fragment to the right namespace.
        $formEl = $dom->appendChild($dom->createElementNS($this->formNs, 'form'));
        $formEl->setAttribute('id',     $this->formName);
        $formEl->setAttribute('method', $this->method);
        $formEl->setAttribute('valid',  $this->valid);
        $str = null;
echo "RAR";
        if (false === $this->valid) {
            echo "HERE";
            $constraintsEl = $formEl->appendChild(
                $dom->createElementNS($this->formNs
            , 'constraints'));

            echo "violation on $fieldName";
            var_dump($this->getMessages());
            foreach ($this->getMessages() as $message) {
                $v = $constraintsEl->appendChild(
                    $dom->createElementNS($this->formNs, 'violation', $message->getMessage())
                );
                $fieldName = sprintf('%s[%s]', $this->formName, $message->getField());

                $v->setAttribute('constraintName', $fieldName);
            }
        }

        $frag = $dom->createDocumentFragment();

        $str;

        // at the current time it doesn't look like you
        // can get the element type attribute easily
        // so we let the element draw it's self
        foreach ($this as $el) {
            $str .= $el->label().PHP_EOL.$el->getElement().PHP_EOL;
        }
        $frag->appendXML($str);

        $formEl->appendChild($frag);
        $dom->documentElement->setAttribute('id', $this->formName);

        return $dom->documentElement;
    }


}
