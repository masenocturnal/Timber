<?php
/** This file contains the class which handles the output xslt
*
* @author    Andrew Mason <andrew@nocturnal.net.au>
* @copyright 2009 Andrew Mason
* @license   http://www.gnu.org/licenses/ GPLv3
*/
/** @brief Facilitates loading of XML Files
*
* This class allows developers to include other xsl files by using
* specially crafted URL's in import statments
*
* xml://project/..../myfile.xsl
* xml://lib/.../myfile.xml
* xml://sys/..../myfile.xsl
*
*
*/
namespace Timber\Streams;

final class XMLStreamLoader extends AbstractStreamLoader
{
    protected static $map;
}
