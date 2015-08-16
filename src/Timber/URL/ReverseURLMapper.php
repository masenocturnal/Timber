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

            error_log("HERE ");

            // for is reserved for the $id
            if (isset($params['for'])) {
                unset($params['for']);
            }
        }
        if (!empty($id)) {

            $params['for'] = $id;
            if($id == 'getProduct') {
                error_log("ID ".$id);
                error_log("STRING ".print_r($params, true));
            }

            try {
                $str = $url->get($params);
            } catch (\Exception $e) {
                error_log($e->getMessage());
            }
        }
        return $str;
    }

}