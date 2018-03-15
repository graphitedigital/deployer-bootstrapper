<?php

namespace Graphite\Deployer\ProjectType;

use Deployer\Deployer;
use Deployer\Task\Context;

class Laravel implements ProjectType
{
    private $recipeDirectory;

    public function setRecipeDirectory($recipeDirectory)
    {
        $this->recipeDirectory = $recipeDirectory;
    }

    public function getSharedFiles()
    {
        return [];
    }

    public function getSharedDirectories()
    {
        return ["public/uploads"];
    }

    public function getWritableDirectories()
    {
        return ["public/uploads", "storage", "bootstrap/cache"];
    }

    public function getCopyDirectories()
    {
        return ["vendor"];
    }

    public function getDeployTasks()
    {
        $this->ensureRecipeTasks();
        $this->registerTasks();

        return [
            "deploy:prepare",
            "deploy:lock",
            "deploy:release",
            "deploy:update_code",
            "deploy:copy_dirs",
            "deploy:shared",
            "deploy:vendors",
            "deploy:writable",
            "graphite:laravel:install_configuration",
            "artisan:storage:link",
            "artisan:view:clear",
            "artisan:cache:clear",
            "artisan:config:cache",
            "artisan:optimize",
            "graphite:laravel:artisan_migrate",
            "deploy:symlink",
            "deploy:unlock",
            "graphite:php:restart",
            "cleanup",
        ];
    }

    public function taskInstallConfiguration()
    {
        $stage = \Deployer\input()->getArgument("stage");
        \Deployer\run("cd {{release_path}} && rm -f .env && ln -s .env.{$stage} .env");
    }

    public function taskArtisanMigrate()
    {
        // Only run "artisan:migrate" once (if deploying to multiple servers)
        static $runOnce = false;
        if ($runOnce) {
            return;
        }

        $runOnce = true;
        $deployer = Deployer::get();
        $deployer->tasks->get("artisan:migrate")->run(Context::get());
    }

    protected function registerTasks()
    {
        \Deployer\task("graphite:laravel:install_configuration", [$this, "taskInstallConfiguration"]);
        \Deployer\task("graphite:laravel:artisan_migrate", [$this, "taskArtisanMigrate"]);
    }

    private function ensureRecipeTasks()
    {
        require_once $this->recipeDirectory . "/laravel.php";
    }
}
