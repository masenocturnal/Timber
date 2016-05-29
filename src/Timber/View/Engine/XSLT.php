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
use Timber\Exceptions\UnexpectedArgumentException;
use \XSLTCache;

class XSLT extends Engine implements EngineInterface, InjectionAwareInterface
{
    public function render($path, $params, $mustClean = null)
    {
        $this->log->debug('Begin Rendering');

        if (!isset($params['dom']) && !empty($params['dom'])) {
            throw new Exception('[XSLT] DOM is empty in: ');
        }

        
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
            if (method_exists($val, '__toXML')) {
                $dom->appendXML($dom->documentElement, $val->__toXML());
            }
        }
        
        libxml_use_internal_errors(true);
        $import  = false;  // used to keep track of  xslt errors

        // make sure the template exists
        if (!file_exists($path)) {
            throw new Exception('Template path '.$path.' does not exist');
        }  else {

            $xsltProcessor = null;

            if (extension_loaded('xslcache')) {

                $cache  = false;
                $config = $this->getDI()['config'];

                if (isset($config->xslt) &&
                    isset($config->xslt->cache)) {
                    $cache = $config->xslt->cache;
                }

                $xsltProcessor = new XSLTCache();
                $xsltProcessor->registerPHPFunctions();


                $import = $xsltProcessor->importStylesheet($path, $cache);

            } else {

                $xslDOM = new \DOMDocument();
                $xslDOM->load($path, LIBXML_XINCLUDE|LIBXML_COMPACT|LIBXML_NONET);

                $xsltProcessor = new \XSLTProcessor();
                $xsltProcessor->registerPHPFunctions();

                $import = $xsltProcessor->importStylesheet($xslDOM);
            }


            if ($xsltProcessor != null ) {
                foreach ($xslParams as $key => $val) {
                    $xsltProcessor->setParameter('', $key, $val);
                }

                // look for the extension
                $format = $this->dispatcher->getParam('format');

                $content = null;
                // probably want a different handler for xml
                if (null != $format && $format == '.xml') {
                    $this->response->setHeader('Content-Type', 'application/xml; charset=utf-8');
                    $dom->formatOutput=true;
                    $dom->preserveWhitespace=true;
                    $content = $dom->saveXML();

                } else {
                    // render as html
                    ob_start();
                    $xsltProcessor->transformToURI($dom, 'php://output');
                    $content = ob_get_clean();
                }
            }
            
            if (!$import) {
                $x = libxml_get_errors();
               
                 foreach ($x as $err) {
                    $this->log->error($err->message);
                    
                 }


                throw new UnexpectedArgumentException(sprintf('Unable to import %s', $path));
            }


            $this->getView()->setContent($content);
            return true;
        }
    }
}
