<?php
namespace Timber;

use Phalcon\Exception;
use Phalcon\Config;
use Phalcon\MVC\Application;

class Timber extends Application
{
    public $configDir     = null;

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
    public function registerClassmap()
    {
        $classMapFile = $this->_dependencyInjector['config']->classMap;

        if (is_file($classMapFile)) {
            $this->loader->registerNamespaces(include($classMapFile), true);
        }
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

            $logger = $this->_dependencyInjector['log'];
            $logger->error(sprintf('Error: %s (%s): %s %s:%s', get_class($e), $e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine()));
            $logger->error($e->getTraceAsString());
            error_log("Exception: ".$e->getMessage());
        }
     }
}
