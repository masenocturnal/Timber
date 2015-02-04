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
use \XSLTCache;

class XSLT extends Engine implements EngineInterface, InjectionAwareInterface
{


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

            $xsltProcessor = null;
            if (extension_loaded('xslcache')) {
                $xsltProcessor = new XSLTCache();
                $xsltProcessor->registerPHPFunctions();
                $xsltProcessor->importStylesheet($path, true);
                
            } else {
            
                $xslDOM = new \DOMDocument();
                $xslDOM->load($path, LIBXML_XINCLUDE|LIBXML_COMPACT|LIBXML_NONET);
                $xsltProcessor = new \XSLTProcessor();
                $xsltProcessor->registerPHPFunctions();
                $xsltProcessor->importStylesheet($xslDOM);
            }

            foreach ($xslParams as $key => $val) {
                $xsltProcessor->setParameter('', $key, $val);
            }

            // look for the extension
            $format = $this->dispatcher->getParam('format');

            $content = null;
            // probably want a different handler for xml
            if (null != $format && $format == '.xml') {
                $this->response->setHeader('Content-Type', 'application/xml; charset=utf-8');
                $content = $dom->saveXML();

            } else {
                // render as html
                ob_start();
                $xsltProcessor->transformToURI($dom, 'php://output');
                $content = ob_get_clean();
            }

            $this->getView()->setContent($content);
            return true;
        }

    }
}
