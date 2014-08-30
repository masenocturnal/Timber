<?php
namespace Timber\Controllers;

use \Phalcon\Mvc\Controller,
    \Phalcon\Mvc\View;
use \Timber\Streams\XMLStreamLoader;
use \Timber\URL\ReverseURLMapper;

/**
 * Base Timber controller. Automatically creates a DOMDocument
 * based on an existing file with the same action name.
 *
 * Also dyamically selects the view directory based on the
 * current controller.
 *
 *
 */
class XMLController extends Controller
{
    /** Reference to the DOM Document **/
    protected $dom              = null;
    
    /** Params passed to xslt processor **/
    protected $xslParams        = null;
    
    /** Reference to the reverseURLMapper **/
    protected $reverseURLMapper = null;
    
    /**
     * Automatically sets the views dir to the module
     * views directory
     *
     * @overload
     */
    public function beforeExecuteRoute($dispatcher)
    {
        $className = $dispatcher->getControllerName();
        $ns        = substr($className,0,strripos($className, '\\', -1)+1);
        $ns        = str_replace(
            [
                'Controllers', // rename Controllers to Views
                '\\' // replace the \ in the namespace with the / from the fs
            ],
            [
                'Views',
                DIRECTORY_SEPARATOR
            ],
            $ns
        );

        $viewsDir = $this->config->appDir.DIRECTORY_SEPARATOR.$ns;
        $this->log->debug('Setting Views Dir to '.$viewsDir);
        $this->view->setViewsDir($viewsDir);

        $this->dom = new \Timber\XML\DOMDocument();
        $this->registerDefaultStreamWrapper();

        $xmlFile = str_replace('Views', 'XML', $ns);
        $xmlFile = $this->config->appDir.DIRECTORY_SEPARATOR.$xmlFile.$dispatcher->getActionName().'.xml';

        $this->log->debug('XML File: '.$xmlFile);

        if (!is_file($xmlFile)) {
            $this->log->debug('XML File does not exist '.$xmlFile);
        }

        // file exist so include
        $this->dom->load($xmlFile, LIBXML_XINCLUDE|LIBXML_COMPACT|LIBXML_NONET);

        // process xinclude statements in the source XML.
        $this->dom->xinclude();

        // we need to populate a few params so that the view xslt can use them
        $this->setDefaultXSLParams();
        
        // instanciate the class so it's accessible to the XSTView
        $this->reverseURLMapper = new ReverseURLMapper($this->url);
    }
    
    /**
     * Sets default param which get passed through to every
     * XSLT processor
     *
     */
    public function setDefaultXSLParams()
    {
        $this->xslParams = [
            'currentURL' => $this->router->getRewriteURI()
        ];

    }


    protected function registerDefaultStreamWrapper()
    {
        $baseDir = $this->config['appDir'];

        // default closures for stream types
        $map = [
            'module' => function($path) use ($baseDir) {
                return $basedir.'/'.$path;
            },
            'lib'    => function($path) {
                // @todo make this work
                return \stream_resolve_include_path(ltrim($path,'/'));
            },
            'default' => getcwd()
        ];
        $streamLoader = new XMLStreamLoader();
        $streamLoader->register('xml', $map, $this->log );
    }


    /**
     * Used to
     * @overload
     *
     */
    public function afterExecuteRoute($dispatcher)
    {
        $this->view->setVar('dom', $this->dom);
        $this->view->setVar('xslParams', $this->xslParams);
        $this->view->pick($dispatcher->getActionName());
    }
}
