<?php
namespace Timber;

use \Phalcon\Exception;
use \Phalcon\Config;


class Timber extends \Phalcon\MVC\Application
{
    public $configDir    = null;
    public $classLoader  = null;
    public $configPrefix = null;
    public $configFile   = null;
    public $config       = null;
    public $loader       = null;
    
    /**
     *
     * @param string $configDir        A valid directory to look at for 
     *                                 config files 
     * @param Object $classLoader      Object responsible for loading classes
     * @param string $configPrefix     Prefix of the file to use to override config
     *                                 options
     * @return int standard unix return codes                 
     */
    public function __construct(\Phalcon\Di $di = null)
    {
        $this->registerModules(
            [
                'Modules' => [
                    'className' => 'Modules\Module',
                    'path'      => '../app/Modules/Module.php',
                ]
            ]
        );
        parent::__construct($di);
    }
    
    
    /**
     * Register an additional classmap if specified in the config
     *
     */
    protected function registerClassmap()
    {
        $classMapFile = $this->_dir['config']->classMap;
        
        if (is_file($classMapFile)) {
            $this->loader->registerNamespaces($classMapFile, true);
        }
    }
    
    public function setClassLoader($loader)
    {
        $this->loader = $loader;
    }
    
    /**
     *
     *
     */
     public function handle($uri = null)
     {
        // we want to catch any errors here
        try {
            
            $this->registerClassMap();        

            $response = parent::handle();
            
            // @todo should we use our own response ?
            if ($response instanceof \Phalcon\Http\Response) {
                return $response->send();
            }
            
            echo $response;
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
     }
}