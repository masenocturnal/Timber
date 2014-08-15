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
                'Modules' => array(
                    'className' => 'Modules\Module',
                    'path'      => '../app/Modules/Module.php',
                )
            ]
        );
        parent::__construct($di);
    }
    
    public function setConfigFile($configFile)
    {
        $this->configFile = $configFile;
    }
    
    public function setConfig(\Phalcon\Config $config)
    {
        $this->config = $config;
    }
    
    /**
     * Responsible for creating and populating the di container
     *
     * @todo look at caching the merged file
     * @todo repace with _registerservices
     */
    protected function _registerServices() 
    {
        
        // Create a DI with the default phalcon services
        $di = new \Phalcon\DI\FactoryDefault();
        
        if (null != $this->config) {
            // attach the configuration object to the di container
            // we need the config before we try and load other parts 
            // which may depend on configuration values
            // @todo make this part of the default timber services
            $di['config'] = $this->config;
            
        }
        
        
        // hold config before we put it into the di container
        $services = [];
        
        // Load the default project container
        $defaultServicesFile = $this->configDir.DIRECTORY_SEPARATOR.'default.services.php';
        
        if (is_file($defaultServicesFile)) {
            $services = require($defaultServicesFile);
        } else {
            throw new Exception('Default Services file '.$defaultServicesFile.' is missing');
        }
        
        $servicesOverride = null;
        
        // look for a host specific override
        if ($this->configPrefix == null) {
            // look for a host specific override
            $servicesOverride = $this->configDir.DIRECTORY_SEPARATOR.'default.services.php';
        } else {
            $servicesOverride = $configDir.DIRECTORY_SEPARATOR.$configPrefix.'services.php';
        }
        
        // look for the file and merge if it's there
        if (is_file($servicesOverride)) {
            $services = array_merge($services, require($servicesOverride));
        }
        
        // now merge them in
        foreach ($services as $key => $val) {
            // true means to register them as shared
            $di->set($key, $val, true);
        }

        return $di;
    }

    /**
     * Find the configuration file in the provided directory
     * 
     * @param string $dir directory where configuration
     * 
     * @return array
     */   
    protected function _registerConfiguration()
    {   
        $configFile = $this->configFile;
        
        if (null == $configFile) {        
            // set this by default. will return null if it doesn't exist
            $configFile = realpath('../app/conf/default.config.php');
        } else {
            // resolve a specifid config file
            $configFile = realpath($configFile);
        }
        
        $configDir    = dirname($configFile); 
        $baseName     = basename($configFile);
        $configPrefix = substr($baseName, 0, strpos($baseName, '.'));
        
        // check for null again as realpath will return null if the file
        // doesn't exist
        if (null == $configFile) {
            throw new Exception('Config file '.$this->configFile.' could not be found');
        }
        
        // look for config.dist.php
        $defaultConfig = $configDir.DIRECTORY_SEPARATOR.'default.config.php';
        if (!is_file($defaultConfig)) {
            throw new Exception('Missing default configuration file '.$defaultConfig);
        }
        
        // load the default config
        $config = new Config(require($defaultConfig));
        
        // look for a host specific override
        if ($configPrefix == null) {
            $this->configPrefix = $configPrefix;
        }
        
        // merge the configs
        if (is_file($configFile)) {
            $config->merge(new Config(require($configFile)));
        }
        
        $this->configDir = $configDir;
        $config['configDir'] = $configDir; // set it in the config too.
        return $config;
    }
    
    /**
     * Register an additional classmap if specified in the config
     *
     */
    protected function registerClassmap()
    {
        $classMap = $this->configDir.DIRECTORY_SEPARATOR.'classmap.php';
        if (is_file($classMap)) {
            $this->loader->registerNamespaces($classMap, true);
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
            $this->config              = $this->_registerConfiguration();
            $this->_dependencyInjector = $this->_registerServices();
            
            $this->registerClassMap();        
            $response = parent::handle();
            
            // @todo should we use our own response ?
            if ($response instanceof \Phalcon\Http\Response) {
                return  $response->send();
            }
            
            echo $response;
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
     }
}