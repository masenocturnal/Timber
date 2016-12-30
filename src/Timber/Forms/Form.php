<?php
namespace Timber\Forms;

use Phalcon\Forms\ElementInterface;
use Timber\EntityInterface;
use Timber\Utils\NSTools;

abstract class Form extends \Phalcon\Forms\Form implements EntityInterface
{
    public $formName = null;
    public $formNs   = 'urn:Timber:Form';
    public $method   = 'post';
    public $valid    = null;
    public $data     = null;
    public $status   = null;

    const STATUS_SUCCESS = 'success';
    const STATUS_FAIL    = 'fail';
    

    /**
     * @override Automatically add set the form
     * when adding the element to the form
     */
    public function add(ElementInterface $element, $position = null, $type = null)
    {
        $element->setForm($this);

        $className  = get_class($this);
        $this->name = NSTools::extractClassname($className);
        $this->ns   = 'urn:'.NSTools::extractNS($className);

        parent::add($element, $position, $type);
    }

    /**
     * @override isValid
     *
     * Overridden so that we can check the form name
     *
     */
    public function isValid($data = null, $entity = null)
    {
        if (null != $this->formName && $data != null) {
            if (isset($data[$this->formName])) {
                $this->_data = $data[$this->formName];
            }
        }
        $this->valid = parent::isValid($this->_data, $entity);

        return $this->valid;
    }

    /**
     * 
     */
    public function setStatus($status)
    {
        if ($status == self::STATUS_SUCCESS ||
            $status == self::STATUS_FAIL) {
            $this->status = $status;
        }
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
        $formEl->setAttribute('status', $this->status);

        $str = null;

        if (false === $this->valid) {
            $constraintsEl = $formEl->appendChild(
                $dom->createElementNS($this->formNs
            , 'constraints'));

            foreach ($this->getMessages() as $message) {
                $v = $constraintsEl->appendChild(
                    $dom->createElementNS($this->formNs, 'violation', $message->getMessage())
                );
                $fieldName = sprintf('%s[%s]', $this->formName, $message->getField());

                $v->setAttribute('fieldName', $fieldName);
                $v->setAttribute('constraintName', $message->getType());
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

        return $dom->saveXML($dom->documentElement);
    }

    public function setLogger($logger)
    {
        $this->_log = $logger;
    }

    public function getNS()
    {
        return $this->ns;
    }

    public function getName()
    {
        return $this->name;
    }
}
