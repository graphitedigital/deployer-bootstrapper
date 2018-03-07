<?php

namespace Graphite\Deployer;

use Deployer\Deployer;
use Graphite\Deployer\ProjectType\ProjectType;

class Bootstrapper
{
    private $options = [];
    private $recipeDirectory = "";
    private $internalTasks;

    public static function register(ProjectType $projectType, $options = [])
    {
        // Load in common tasks/similar from Deployer
        $recipeDirectory = self::getRecipeDirectory();
        $projectType->setRecipeDirectory($recipeDirectory);

        // Return an instance of this class post-configuration
        $instance = new self;
        $instance->mergeOptions($options);

        $instance->recipeDirectory = $recipeDirectory;
        $instance->loadCommonTasks();

        $instance->determineRepository();
        $instance->configureSSH();
        $instance->configureReleases();
        $instance->configureInventory();
        $instance->configureForProjectType($projectType);

        // Unlock after failed deploys to allow retries
        \Deployer\after("deploy:failed", "deploy:unlock");

        return $instance;
    }

    private static function getRecipeDirectory()
    {
        // Get Deployer recipe directory
        $reflection = new \ReflectionClass(Deployer::class);
        $filename = $reflection->getFileName();
        $directory = basename(dirname($filename));

        return $directory . "/../recipe";
    }

    private function mergeOptions(array $options)
    {
        $default = [];
        $this->options = array_merge($default, $options);
    }

    private function loadCommonTasks()
    {
        require_once $this->recipeDirectory . "/common.php";
        $this->internalTasks = new Tasks();
        $this->internalTasks->registerTasks();
    }

    private function determineRepository()
    {
        if (isset($this->options["repository"])) {
            \Deployer\set('repository', $this->options["repository"]);

            return;
        }

        $returnStatus = 0;
        $output = [];
        exec("git remote get-url origin 2>/dev/null", $output, $returnStatus);
        if ($returnStatus !== 0) {
            throw new \RuntimeException("No git repository found");
        }
        $repository = trim($output[0]);
        \Deployer\set('repository', $repository);
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
