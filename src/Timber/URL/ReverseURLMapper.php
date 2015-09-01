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
    
    public function __construct(Url $url)
    {
        self::$url = $url;
    }
    
    public static function map($id, $args = null)
    {
        $url    = self::$url;
        $params = [];
        $str    = '';

        if ($args == null) {
            $params = [];
        } else {
            mb_parse_str($args, $params);

            // for is reserved for the $id
            if (isset($params['for'])) {
                unset($params['for']);
            }
        }
        if (!empty($id)) {

            $params['for'] = $id;

            try {
                $str = $url->get($params);
            } catch (\Exception $e) {
                error_log($e->getMessage());
            }
        }
        return $str;
    }

}