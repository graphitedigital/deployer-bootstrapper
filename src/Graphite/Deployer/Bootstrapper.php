<?php

namespace Graphite\Deployer;

use Graphite\Deployer\ProjectType\ProjectType;

class Bootstrapper
{
    private $options;
    private $repository;

    public static function register(ProjectType $projectType, $options = [])
    {
        // Return an instance of this class post-configuration
        $instance = new self;
        $instance->mergeOptions($options);

        $instance->determineRepository();
        $instance->configureSSH();
        $instance->configureReleases();
        $instance->configureInventory();
        $instance->configureForProjectType($projectType);

        return $instance;
    }

    private function mergeOptions(array $options)
    {
        $default = [];
        $this->options = array_merge($default, $options);
    }

    private function determineRepository()
    {
        $returnStatus = 0;
        $output = [];
        exec("git remote get-url origin 2>/dev/null", $output, $returnStatus);
        if ($returnStatus !== 0) {
            throw new \RuntimeException("No git repository found");
        }
        $this->repository = trim($output[0]);
        \Deployer\set('repository', $this->repository);
    }

    private function configureSSH()
    {
        \Deployer\set('ssh_type', 'native');
        \Deployer\set('ssh_multiplexing', false);
    }

    private function configureReleases()
    {
        \Deployer\set('keep_releases', 3);
    }

    private function configureInventory()
    {
        \Deployer\inventory(getcwd() . "/config/deploy/servers.yml");
    }

    private function configureForProjectType(ProjectType $projectType)
    {
        \Deployer\set("shared_dirs", $projectType->getSharedDirectories());
        \Deployer\set("shared_files", $projectType->getSharedFiles());
        \Deployer\set("writable_dirs", $projectType->getWritableDirectories());
        \Deployer\set("copy_dirs", $projectType->getCopyDirectories());

        // Task configuration
        \Deployer\task("deploy", $projectType->getDeployTasks());
    }
}
