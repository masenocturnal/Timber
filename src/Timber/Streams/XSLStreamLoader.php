<?php
/** This file contains the class which handles the output xslt
*
* @author    Andrew Mason <andrew@nocturnal.net.au>
* @copyright 2009 Andrew Mason
* @license   http://www.gnu.org/licenses/ GPLv3
*/
/** @brief Facilitates loading of XSLT Files
*
* This class allows developers to include other xsl files by using
* specially crafted URL's in import statments
*
* xsl://lib/..../myfile.xsl
* xsl://sys/..../myfile.xsl
* xsl://modulename/myfile.xsl
*
*/
namespace Timber\Streams;

final class XSLStreamLoader extends AbstractStreamLoader
{
    protected static $map;
    protected static $logName;
}
