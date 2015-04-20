<?php

namespace Rocketeer\Plugins\Wordpress\Services;

use Illuminate\Container\Container;
use Illuminate\Support\Arr;
use Rocketeer\Bash;
use Rocketeer\Traits\HasLocator;

/**
 * Helper to sync files
 *
 * Reuses some code and principles from Rocketeer\Strategies\Deploy\SyncStrategy.php
 */
class SyncHandler
{
    use HasLocator;

    /**
     * @var Illuminate\Container\Container The application container 
     */
    protected $app;

    /**
     * @var string
     */
    protected $port;

    /**
     * @var bool
     */
    protected $useSshAsRemoteShell = true;

    /**
     * @var array The default options to use with the rsync command
     */
    protected $defaultOptions = [
        '--times'       => null, 
        '--verbose'     => null, 
        '--recursive'   => null, 
        '--compress'    => null
    ];

    /**
     * Constructor.
     */
    public function __construct (Container $app) 
    {
        $this->app = $app;
    }

    /**
     * Set default options to pass to rsync command
     *
     * @param array $options
     *
     * @return Rocketeer\Plugins\Wordpress\Services\SyncHandler
     */
    public function setDefaultOptions (array $options) 
    {
        $this->defaultOptions = $options;
        return $this;
    }

    /**
     * Whether to use ssh as remote shell or not
     *
     * @param boolean
     *
     * @return Rocketeer\Plugins\Wordpress\Services\SyncHandler
     */
    public function useSshAsRemoteShell ($use=true)
    {
        $this->useSshAsRemoteShell = (boolean) $use;
        return $this;
    }

    /**
     * Push local files to remote destination
     *
     * @param array $localFiles The files to sync to the remote destination
     * @param string $remoteDestination The remote destination which receives the local files
     * @param array $options Extra options to pass to the rsync command
     *
     * @return boolean
     */
    public function push (array $localFiles, $remoteDestination=null, array $options=array())
    {
        // prepend remote handle to destination
        if ($handle = $this->getRemoteHandle()) {
            $remoteDestination = $handle.':'.$remoteDestination;
        }

        return $this->go('push', $localFiles, $remoteDestination, $options);
    }

    /**
     * Pull remote files to local destination
     *
     * @param array $remoteFiles The files to sync to the local destination
     * @param string $localDestination The local destination which receives the remote files
     * @param array $options Extra options to pass to the rsync command
     *
     * @return boolean
     */
    public function pull (array $remoteFiles, $localDestination=null, array $options=array())
    {
        // prepend remote handle to sources
        if ($handle = $this->getRemoteHandle()) {
            foreach ($remoteFiles as &$source) {
                $source = $handle.':'.$source;
            }
        }

        return $this->go('pull', $remoteFiles, $localDestination, $options);
    }

    /**
     * Get the handle to connect with
     *
     * @return string
     */
    protected function getRemoteHandle()
    {
        $credentials    = $this->connections->getServerCredentials();
        $handle         = array_get($credentials, 'host');
        $explodedHandle = explode(':', $handle);
        
        // Extract port
        if (count($explodedHandle) === 2) {
            $this->port = $explodedHandle[1];
            $handle     = $explodedHandle[0];
        }
        
        // Add username
        if ($user = array_get($credentials, 'username')) {
            $handle = $user.'@'.$handle;
        }
        
        return $handle;
    }

    /**
     * Get the transport to connect through
     * 
     * @return string
     */
    protected function getSshTransport()
    {
        $ssh = 'ssh';
        
        // Get port
        if ($port = $this->getOption('port', true) ?: $this->port) {
            $ssh .= ' -p '.$port;
        }
        
        // Get key
        $key = $this->connections->getServerCredentials();
        $key = Arr::get($key, 'key');
        if ($key) {
            $ssh .= ' -i '.$key;
        }
        
        return $ssh;
    }

    /**
     * Go execute the rsync command
     *
     * @param string $action The method to call on the rsync binary, can be either 'push' or 'pull'
     * @param array $sourceFiles The files to use as source
     * @param array $destination The path to use as destination
     * @param array $options Extra options to pass to the rsync command
     *
     * @return boolean
     */
    protected function go ($action, array $sourceFiles, $destination, array $options=array()) 
    {
        if (!in_array($action, ['push', 'pull'])) {
            return false;
        }

        // check if source files are defined
        if (count($sourceFiles) < 1) {
            return false;
        }

        // set ssh as remote shell
        if ($this->useSshAsRemoteShell) {
            $options['--rsh'] = $this->getSshTransport();
        }

        $rsync = $this->builder->buildBinary('rsync');
        $cmd = $rsync->getCommand(
            null, 
            array_merge($sourceFiles, [$destination]),
            array_merge($this->defaultOptions, $options)
        );

        // and run it
        return $this->bash->onLocal(function (Bash $bash) use ($cmd) {
            $bash->run('cd '.$this->paths->getBasePath());
            return $bash->run($cmd);
        });
    }
}