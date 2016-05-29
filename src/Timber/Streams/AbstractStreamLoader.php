<?php
/** 
*
* This class allows developers to include other xsl files by using
* specially crafted URL's in import statments
*
* xsl://lib/..../myfile.xsl
* xsl://sys/..../myfile.xsl
* xsl://modulename/myfile.xsl
* 
* @author    Andrew Mason <andrew@nocturnal.net.au>
* @copyright 2009 Andrew Mason
* @license   http://www.gnu.org/licenses/ GPLv3
*/
namespace Timber\Streams;

use Phalcon\Logger\AdapterInterface as Logger;


abstract class AbstractStreamLoader
{

    public $pos;
    public $fp;
    public $path;
    protected static $logger;



    /**
     * Sets the mapping from
     *
     */
    public function setPathMap(array $map)
    {
        // do we check for callable ?
        if (isset($map['default'])) {
            static::$map = $map;
        } else {
            throw new \Exception('No [default] key specified in path map for '.get_class($this));
        }
    }

    /**
     *
     *
     */
    public function appendPathToMap($prefix, $path)
    {
        static::$map[$prefix] = $path;
    }


    /**
     *
     *
     */
    public function getMap()
    {
        return static::$map;
    }

    /**
     *
     *
     *
     */
     public function register($scheme, array $map, Logger $logger )
     {
        
        if (!in_array($scheme,stream_get_wrappers()) ) {
            self::$logger = $logger;
            $this->setPathMap($map);

            // this allows us to load XML via xml://lib/ style urls
            stream_wrapper_register($scheme, get_class($this));
        }
     }

    /**
     *
     *
     */
    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $myClassName = get_class($this);
        $myClassName = substr($myClassName,strrpos($myClassName,'\\')+1);

        $this->pos  = 0;
        $urlParts = \parse_url( $path );

        if (3 == sizeof($urlParts)) {
            $this->path = $this->getPath($urlParts['host'], $urlParts['path']);
            
            if ($this->path != null && \stream_resolve_include_path($this->path) !== false) {
                $this->fp = fopen($this->path, 'r');
                self::$logger->debug('Including '.$this->path);

                return true;
            } else {
                self::$logger->debug('Path for '.$path.' is null ');
            }
        }

        return false;
    }

    /**
     *
     *
     */
    public function getPath($host, $path)
    {

        $ret;
        if (static::$map != null) {
            // use the default when we haven't been provided anything
            if (!isset(static::$map[$host])) {
                self::$logger->warning(
                    sprintf('No mapping provided for %s using the default', $host)
                );
                $host = 'default';
            }
            
            if (is_string(static::$map[$host])) {
                $ret = static::$map[$host].'/'.$path;
            } elseif (is_callable(static::$map[$host])) {
                $x = static::$map[$host];
                $ret = $x($path);
            }
        }
        self::$logger->debug("Stream  resolved $path to $ret ");

        return $ret;
    }

    /**
     *
     *
     */
    public function stream_read($count)
    {
        if ($this->fp != null) {
            $this->pos += $count;

            return fgets( $this->fp, $count );
        }

        return false;
    }

    /**
     *
     *
     */
    public function url_stat()
    {
        return array();
    }

    /**
     *
     *
     */
    public function stream_eof()
    {
        if ($this->fp != null) {
            return feof($this->fp);
        }
    }

    /**
     *
     *
     */
    public function stream_tell()
    {
        return $this->pos;
    }

    /**
     *
     *
     */
    public function stream_close()
    {
        if ($this->fp != null) {
            return fclose( $this->fp );
        }
    }
}
