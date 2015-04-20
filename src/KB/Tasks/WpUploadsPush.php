<?php

namespace Rocketeer\Plugins\Wordpress\Tasks;

/**
 * Push uploads directory to remote server
 *
 * @TODO implement some kind of backup mechanism to prevent losing files
 */
class WpUploadsPush extends \Rocketeer\Abstracts\AbstractTask
{
    protected $description = 'Push uploads to remote';

    public function execute ()
    {
        $filesToPush = $this->config->get('rocketeer-wordpress::config.uploads_dir', []);
        $filesToPush = is_array($filesToPush) ? $filesToPush : [$filesToPush];

        $this->syncHandler->push(
            $filesToPush,
            $this->releasesManager->getCurrentReleasePath().'/',
            array('--relative' => null)
        );
    }
}