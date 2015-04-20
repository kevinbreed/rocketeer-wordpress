<?php

namespace Rocketeer\Plugins\Wordpress;

use Illuminate\Container\Container;
use Rocketeer\Abstracts\AbstractPlugin;
use Rocketeer\Facades\Rocketeer;
use Rocketeer\Services\TasksHandler;

use Rocketeer\Plugins\Wordpress\Services;
use Rocketeer\Plugins\Wordpress\Tasks;


class RocketeerWordpress extends AbstractPlugin
{
    /**
     * Constructor
     */
    public function __construct (Container $app)
    {
        parent::__construct($app);

        // define config folder, it will be available through $this->config('rocketeer-wordpress::config')
        $this->configurationFolder = __DIR__.'/../config';

        // include all binaries
        foreach (glob(__DIR__."/Binaries/*.php") as $filename) {
            require_once $filename;
        }

        // add tasks
        Rocketeer::add('Rocketeer\Plugins\Wordpress\Tasks\WpDbPush');
        Rocketeer::add('Rocketeer\Plugins\Wordpress\Tasks\WpUploadsPush');
        Rocketeer::add('Rocketeer\Plugins\Wordpress\Tasks\WpUploadsPull');
    }

    /**
     * Bind additional classes to the app container
     */
    public function register(Container $app)
    {
        // bind sync handler
        $app->bind('syncHandler', function ($app) { 
            return new Services\SyncHandler($app);
        });

        return $app;
    }

    /**
     * Register tasks
     */
    public function onQueue(TasksHandler $queue)
    {
        // ...
    }
}
