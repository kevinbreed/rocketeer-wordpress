<?php

namespace Rocketeer\Plugins\Wordpress\Tasks;

/**
 * Pull uploads directory from remote server
 *
 * @TODO implement some kind of backup mechanism to prevent losing files
 */
class WpUploadsPull extends \Rocketeer\Abstracts\AbstractTask
{
    protected $description = 'Pull uploads from remote';

    public function execute ()
    {
    	$filesToPull = $this->config->get('rocketeer-wordpress::config.uploads_dir', []);
        $filesToPull = is_array($filesToPull) ? $filesToPull : [$filesToPull];
        $releasePath = rtrim($this->releasesManager->getCurrentReleasePath(), '/');
        $workingPath = rtrim($this->paths->getBasePath(), '/');

        // pull specified paths individually to avoid mixing up paths
        foreach ($filesToPull as $file) {
            $this->syncHandler->pull(
                [rtrim($releasePath.'/'.$file, '/').'/'],
                $workingPath.'/'.$file
            );
        }
    }
}