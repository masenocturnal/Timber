<?php
/** This file contains an extended DomDocument
 *
 * @author    Andrew Mason <andrew@nocturnal.net.au>
 * @copyright 2009 Andrew Mason
 * @license   http://www.gnu.org/licenses/ GPLv3
 */
/** @brief Timbers DOMDocument extension
 *
 */
namespace Timber\XML;

use \DOMException;
use Timber\Config\ConfigReader;
use Phalcon\Logger\AdapterInterface as Logger;
use Timber\Streams\XMLStreamLoader;
use Timber\XML\DOMElement;

/**
 * This is an extension of the DOMDocument provided by libxml (via the php extensions).
 * It adds some convenience methods to make adding xml and xhtml document fragments and
 * also provides an in build xpath processor which is instanciated on first access
 *
 */
final class DOMDocument extends \DOMDocument
{
    private $defaultNamespace     = null;                         ///< the default namespace of the document
    private $xpath                = null;                         ///< DOMXpath processor
    private $namespaces           = null;                         ///< associative array of prefixes and namespace URIs
    private $prefixlessNamespaces = null;                         ///< array of namespace URIs that dont have a prefix
    public $debug                 = false;                        ///< True to turn on debugging
    public $currentNamespace      = null;                         ///< Namespace that is used by default
    public $fullNSSearch          = false;                        ///< Look for namespaces in child nodes
    public $logger                = null;                         ///< Logger
    public static $streamLoader   = null;                         ///< registered stream loader

    const   XHTML_NS              = 'http://www.w3.org/1999/xhtml';  ///< xhtml namespace

    /**
     * Constructor
     *
     * @param string $version XML version no
     * @param string $enc     Encoding type. Defaults to UTF-8
     *
     * @return void
     */
    public function __construct($version = null, $enc = null)
    {
        if ($version == null) {
            $version = '1.0';
        }
        if ($enc == null) {
            $enc = 'UTF-8';
        }

        parent::__construct( $version, $enc );
        parent::registerNodeClass('DOMElement', '\Timber\XML\DOMElement');

        $this->preserveWhiteSpace = false;
        $this->substituteEntities = false;
        $this->recover            = false;
    }
    

    /**
     *
     *
     */
    public function getXpath()
    {
        if ($this->xpath == null) {
            $this->createXPath();
        }

        return $this->xpath;
    }

    /**
     *
     *
     */
    public function getDefaultNamespace()
    {
        if ($this->defaultNamespace == null) {
            if ( $this->isDefaultNamespace( $this->documentElement->namespaceURI) ) {
                $this->defaultNamespace = $this->documentElement->namespaceURI;
            }
        }

        return $this->defaultNamespace;
    }

    public function getNamespaces()
    {
        if ($this->namespaces == null) {
            $this->setNamespaces();
        }

        return $this->namespaces;
    }

    /**
     * Finds and returns all of the namespaces in a DOMDocument which do not have
     * a dedicated prefix.
     *
     * Potentially expensive of large documents.
     *
     *
     * @return array
     */
    public function getPrefixlessNamespaces()
    {
        if ($this->prefixlessNamespaces == null) {
            foreach ($this->xpath->query('//namespace::*') as $node) {
                $this->prefixlessNamespaces[] = $node->nodeValue;
                $this->prefixlessNamespaces   = array_unique($this->prefixlessnamespaces);
            }
        }

        return $this->prefixlessNamespaces;
    }

    /**
     * create the xpath object and register any namespace prefixes
     *
     * @return void
     */
    private function createXPath()
    {
        $this->xpath = new \DOMXpath($this);
        if (is_null($this->namespaces)) {
            $this->setNamespaces();
        }
        foreach ($this->namespaces as $prefix => $uri) {
            $this->xpath->registerNamespace($prefix, $uri);
        }
    }

    /**
     * Generate Arrays of namespace uris with and without prefixes
     *
     * @return void
     */
    private function setNamespaces()
    {
        $this->namespaces = [];

        // will create the processor if needed
        $xpath      = $this->getXpath();
        $namespaces = $xpath->query('namespace::*');

        foreach ($namespaces as $ns) {
            if ($ns->prefix == null) {
                $this->defaultNamespace = $ns->namespaceURI;
                $this->currentNamespace = $ns->namespaceURI;
            } elseif ($ns->prefix != 'xml') {
                $this->namespaces[$ns->prefix] = $ns->namespaceURI;
            }
        }
    }

    /**
     * Create an element after making sure that all codepoints
     * are safe for XML inclusion
     *
     * @param string $name  Name of the element to create
     * @param string $value optional
     *
     * @return DOMElement
     */
    public function createElement($name, $value = null)
    {
        $el   = null;
        $name = strval($name);

        if ($name != null && $value != null) {
            $value = htmlspecialchars(strval($value), ENT_QUOTES|ENT_XML1);
        }

        try {
            if ($this->currentNamespace == null) {
                $el = parent::createElement($name, $value );
            } else {
                $el = parent::createElementNS($this->currentNamespace, $name, $value);
            }
        } catch ( DOMException $e ) {
            $this->log( '[DOM] Exception '.$e->getMessage() );
        }

        return $el;
    }

    /**
     * Create an element without filtering. Use with caution
     *
     * @param string $name  Name of element to create
     * @param string $value Value of the element
     *
     * @return void
     */
    public function createRawElement( $name, $value = null )
    {
        try {
            parent::createElement( $name, $value );
        } catch ( DOMException $e ) {
            $this->log( '[DOM] Exception '.$e->getMessage() );
        }

    }

    /**
     * Xpath query for a single node
     *
     * @param string     $query valid XPath expression
     * @param DOMElement &$node DomElement from which to start searching
     *
     * @return DOMElement
     */
    public function getNode($query, DOMElement $node = null, array $params = null)
    {
        $nodes = null;

        if ($this->xpath == null) {
            $this->createXPath();
        }

        if ($this->xpath) {
            if ($params != null) {
                $query = $this->compileXPath($query, $params);
            }
            
            if ($query != null) {
                if ($node == null) {
                    $nodes = $this->xpath->query($query);
                } else {
                    $nodes = $this->xpath->query($query, $node);
                }

                if ($nodes != null && $nodes !== false && 1 == $nodes->length) {
                    return $nodes->item(0);
                }
            }
        }

        return null;
    } //

    /**
     * Returns a nodeset
     *
     * @param string     $query Xpath Query
     * @param DomElement &$node Element from which to start searching.
     *
     * @return DOMNodes
     */
    public function getNodes( $query, DOMElement &$node = null)
    {
        $nodes = null;

        if ($this->xpath == null) {
            $this->createXPath();
        }

        if ($this->xpath) {

            if ($node == null) {
                $nodes = $this->xpath->query($query);
            } else {
                $nodes = $this->xpath->query($query, $node);
            }

            return $nodes;
        } else {
            $this->log("Cannot perform get_node as either the xml or the xpath object are null");
        }

        return null;
    } // end get_node

    /**
     * Allows you to insert abitrary well formed XML into  a dom tree.
     *
     * @param DOMElement &$el    Element to which the XML string is appended
     * @param string     $string XML in string form
     *
     * @return void
     */
    public function appendXML(DOMElement &$el, $string, $replace =  false)
    {
        $frag = $this->createDocumentFragment();

        if ($frag->appendXML($string)) {
            if ($replace === true) {
                $parent = $element->parentNode;
                $parent->replaceChild($frag, $el);
            } else {
                $el->appendChild($frag);
            }

            return $el->firstChild;
        }

        return null;
    }
    
    public function importXML($xpath, $string)
    {
    
    }

    /**
     *  At the moment this is just a wrapper that calls appendXML.
     *
     * @param DOMElement &$el    Element to append the xml to
     * @param string     $string XML string
     *
     * @todo needs testing
     *
     * @return array
     */
    public function appendXHTML(DOMElement &$el, $string )
    {
        $string = htmlspecialchars($string, ENT_QUOTES|ENT_XML1);

        // I can't seem to find a nice way to specify that a fragment is from a particular
        // namespace
        $string = '<body xmlns="'.self::XHTML_NS.'" >'.$string.'</body>';
        $frag = $this->createDocumentFragment();
        $frag->appendXML($string);

        // the element hasn't been attached to the document yet so we can just replace it
        foreach ($frag->childNodes as $childNode) {
            $node = $this->importNode( $childNode, true );
            $el->appendChild( $node );
        }

        $errors = libxml_get_errors();

        return empty( $errors );
    }

    /**
     * Iterates through a list of element names and
     * if a corresponding key is found in the array it creates an element
     * with the same name as the key , and adds it to the parent node
     *
     * @param  DOMElement $parentEl    the element to which all elements will get added
     * @param  array      $data        contains the data to be added
     * @param  string     $list        comma seperated list of keys which will be used to create elements
     * @param  boolean    $excludeList If set to true the provided list operates as an exclude list
     * @return DOMElement Returns the parent element that was passed to the method
     */
    public function addAsElements( DOMElement $parentEl , array $data , $list = null, $excludeList = false)
    {
        if ($list == null) {
            // add the elements in the array
            foreach ($data as $name=>$data) {
                $parentEl->appendChild( self::createElement( $name, $data ) );
            }
        } elseif ($excludeList == true) {
            self::addAsElementsNotInList( $parentEl, $data, $list, $excludeList );
        } else {
            self::addAsElementsList( $parentEl, $data, $list, $excludeList );
        }

        return $parentEl;
    }

    /**
     * Creates a series of DOMElements and their corresponding values from a comma seperated list.
     *
     * @param DOMElement $parentEl Element to add the nodes to
     * @param array      $data     Associative array of keys and values are available to add to the DomElement
     * @param list       $list     Comma seperated list of keys which are in the array which should be
     *                             added to the DomElement
     *
     * @return void
     */
    private function addAsElementsList( DOMElement $parentEl ,array $data, $list   )
    {
        $listArr = explode( ',', $list );

        foreach ($listArr as $name) {
            if ( isset($data[ $name ]) ) {
                $parentEl->appendChild(self::createElement($name, $data[ $name ]));
            }
        }
    }

    /**
     * Creates a series of DOMElements and their corresponding values from the key / values of an array
     * and does not attempt to include any key named in the list
     *
     * @param DOMElement $parentEl Element to add the nodes to
     * @param array      $data     Associative array of keys and values are available to add to the DomElement
     * @param list       $list     Comma seperated list of keys which are in the array which should be
     *                             added to the DomElement
     *
     * @return void
     */
    private function addAsElementsNotInList( DOMElement $parentEl, array $data, $list )
    {
        $listArr = explode(',', $list );

        foreach ($data as $key=>$value) {
            if (!in_array($key, $listArr)) {
                $parentEl->appendChild(self::createElement($key, $value));
            }
        }
    }
    
    

    /**
     * Provides the same functionality as in_array but
     * for DOMNodeLists
     *
     * @param DOMNodeList $haystack DomList to search through
     * @param string      $needle   Item to look for
     *
     * @return boolean
     */
    public static function inList(\DOMNodeList $haystack, $needle )
    {
        foreach ($haystack as $hay) {
            if ($needle == $hay->nodeValue) {
                return true;
            }
        }

        return false;
    }

    /**
     * This can be replaced later to provide logging functionality
     * Right now this will at least create a standard way of logging
     *
     * @param string $msg Message to log
     *
     * @return void
     */
    protected function log($msg, $level)
    {
        if ($this->logger != null) {
            $this->logger->debug($msg, $level);
        }
    }

    /**
     * Will 'compile' and xpath expression so a given query
     *
     *  /t:foo/t:bar[@id='{myid}']
     */
    protected function compileXPath($query, $params)
    {
        // make sure the params don't contain in injection attacks
        // @todo migrate this to use phalcon or be self contained
        // array_walk($params,'\Timber\Utils\Validator::filterForXPathQuery');
        
        // array_walk alters the internal pointer
        reset($params);

        $replace = [];
        foreach ($params as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }

        // interpolate replacement values into the message and return
        return strtr($query, $replace);
    }
}
