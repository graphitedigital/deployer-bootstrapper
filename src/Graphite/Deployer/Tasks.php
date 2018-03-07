<?php

namespace Graphite\Deployer;

class Tasks
{
    public function registerTasks()
    {
        \Deployer\task("graphite:php:restart", [$this, "taskRestartPHP"]);
    }

    public function taskRestartPHP()
    {
        $cmd = "systemctl list-unit-files | grep php | grep fpm | cut -d' ' -f1";
        $phpVersion = \Deployer\run($cmd);
        if (substr($phpVersion, 0, 3) !== "php") {
            throw new \RuntimeException("PHP service not found");
        }
        \Deployer\run("sudo systemctl restart $phpVersion");
    }
}
