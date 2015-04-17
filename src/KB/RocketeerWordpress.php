<?php

namespace KB;

use Illuminate\Container\Container;
use Rocketeer\Services\TasksHandler;
use Rocketeer\Abstracts\AbstractPlugin;

class RocketeerWordpress extends AbstractPlugin
{
    /**
     * Bind additional classes to the app container
     */
    public function register(Container $app)
    {
        return $app;
    }

    /**
     * Register tasks
     */
    public function onQueue(TasksHandler $queue)
    {

    }
}