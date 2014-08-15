<?php
namespace Timber\View\Engine;
use Phalcon\Mvc\View\EngineInterface;
use Phalcon\DI\Injectable;
use Phalcon\Mvc\View\Engine;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\DI\InjectionAwareInterface;
use Phalcon\Mvc\ViewInterface;
use Phalcon\DiInterface;
use Phalcon\Exception;

class XSLT extends Engine implements EngineInterface, InjectionAwareInterface
{

    public function __construct($view, $dependencyInjector = null)
    {
        parent::__construct($view, $dependencyInjector);
    }

    public function getContent ()
    {
        return $this->content;
    }

    public function partial($partialPath)
    {
        parent::__partial($partialPath);
    }


    public function render($path, $params, $mustClean = null)
    {

        $dom = $params['dom'];
        unset($params['dom']);

        $xslParams = null;
        // look for any special params
        if (isset($params['xslParams'])) {
            $xslParams = $params['xslParams'];
            unset($params['xslParams']);
        }

        foreach ($params as $key => $val) {
            // @todo do we import nodes ?
            if (method_exists($val,'__toXML')) {
                $dom->appendXML($dom->documentElement, $val->__toXML());
            }
        }

        if ($dom == null) {
            throw new Exception('DOM is empty in: '.__CLASS__);
        }

        // make sure the template exists
        if (!file_exists($path)) {
            throw new Exception('Template path '.$path.' does not exist');
        }  else {

            $this->log->debug('File '.$path.' exists');

            $xslDOM = new \DOMDocument();
            $xslDOM->load($path, LIBXML_XINCLUDE|LIBXML_COMPACT|LIBXML_NONET);

            // create the xslt processor
            $xsltProcessor = new \XSLTProcessor();
            $xsltProcessor->importStylesheet($xslDOM);
            foreach ($xslParams as $key => $val) {
                $xsltProcessor->setParameter('', $key, $val);
            }

            // look for the extension
            $format = $this->dispatcher->getParam('format');

            $content = null;
            // probably want a different handler for xml
            if (null != $format && $format == '.xml') {
                $content = $dom->saveXML();

            } else {
                // render as html
                //ob_start();
                $xsltProcessor->transformToURI($dom, 'php://output');
                //$content = ob_get_clean();
            }

            $this->getView()->setContent($content);
            return true;
        }

    }
}
