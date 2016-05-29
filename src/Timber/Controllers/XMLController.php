<?php
namespace Timber\Controllers;

use Phalcon\Mvc\Controller,
    Phalcon\Mvc\View,
    Timber\Streams\XMLStreamLoader,
    Timber\URL\ReverseURLMapper,
    Timber\Exceptions\FileNotFoundException,
    Timber\XML\DOMDocument;

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

    protected $defaultModule    = 'Modules\Site';

    protected $xmlFile          = null;

    protected $xslFile          = null;

    protected $moduleDir        = null;

    /**
     * Automatically sets the views dir to the module
     * views directory
     *
     * @overload
     */
    public function beforeExecuteRoute($dispatcher)
    {
        $className = $dispatcher->getControllerName();
        $ns        = substr($className, 0, strripos($className, '\\', -1)+1);
        $appDir    = $this->config->appDir;

        $this->xslFile = $dispatcher->getActionName();

        // turn Modules/Foo/Controllers/ into Modules/Foo/
        $this->moduleDir = str_replace(
            '\\',
            DIRECTORY_SEPARATOR,
            substr($ns, 0, strrpos($ns, 'Controllers', 0)
        ));

        $viewsDir = $appDir.DIRECTORY_SEPARATOR.$this->moduleDir.'Views'.DIRECTORY_SEPARATOR;
        $this->log->debug('Setting Views Dir to '.$viewsDir);
        $this->view->setViewsDir($viewsDir);

        $loadOrder = [
            $this->xmlFile,
            $appDir.DIRECTORY_SEPARATOR.$this->moduleDir.'XML'.DIRECTORY_SEPARATOR.$dispatcher->getActionName().'.xml',
            $appDir.DIRECTORY_SEPARATOR.
                str_replace('\\', '/', $this->defaultModule).
                DIRECTORY_SEPARATOR.'XML'.DIRECTORY_SEPARATOR.'index.xml'
        ];

        $this->loadXML($loadOrder);

        // we need to populate a few params so that the view xslt can use them
        $this->setDefaultXSLParams();

        // instanciate the class so it's accessible to the XSTView
        // todo move to Di ?
        $this->reverseURLMapper = new ReverseURLMapper($this->url);
    }

    protected function loadXML($loadOrder)
    {
        $this->dom = new DOMDocument();
        $this->registerDefaultStreamWrapper();

        $xmlFile = null;

        foreach ($loadOrder as $file)
        {
            if ($file != null && is_file($file)) {
                $this->log->debug(sprintf('Attempting to load %s', $file));
                $xmlFile = $file;
                break;
            }
        }

        if ($xmlFile == null) {
            throw new FileNotFoundException('XML File does not exist');
        }

        $this->log->debug('XML File: '.$xmlFile);

        // file exist so include
        $this->dom->load($xmlFile, LIBXML_XINCLUDE|LIBXML_COMPACT|LIBXML_NONET);

        // process xinclude statements in the source XML.
        $this->dom->xinclude();

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

        // @todo get this from the DI
        // default closures for stream types
        $map = [
            'modules' => function($path) use ($baseDir) {
                return $baseDir.'/'.$path;
            },
            'lib'    => function($path) {
                // @todo make this work
                return \stream_resolve_include_path(ltrim($path,'/'));
            },
            'app'     => rtrim($this->config->appDir, '/'),
            'default' => getcwd()
        ];
        $streamLoader = new XMLStreamLoader();
        $streamLoader->register('xml', $map, $this->log);
    }


    /**
     * Used to
     * @overload
     *
     */
    public function afterExecuteRoute($dispatcher)
    {
        
        $this->log->debug('[XMLController] Current working dir '.getcwd());
        $this->log->debug('[XMLController] Views Dir: '.$this->view->getViewsDir());
        
        $this->view->setVar('dom', $this->dom);
        // $this->log->debug('[XMLController] Document: '.$this->dom->saveXML());
        
        $this->view->setVar('xslParams', $this->xslParams);
        $this->view->pick($this->xslFile);
        
    }
}