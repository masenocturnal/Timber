<?php
namespace Timber\URL;

use Phalcon\Mvc\Url;

/**
 * Provide 
 *
 */
class ReverseURLMapper 
{
    public static $url;
    
    public function __construct(URL $url)
    {
        self::$url = $url;
    }
    
    public static function map($id, array $params = null)
    {
        $url = self::$url;
        $str = '';
        if (!empty($id)) {
            try {
                $str = $url->get(array(
                    'for' => strval($id),
                    'year' => 2012,
                    'month' => '01',
                    'title' => 'some-blog-post'
                ));
            } catch (\Exception $e) {
                //error_log($e->getMessage());
            }
        }        
        return $str;
    }

}