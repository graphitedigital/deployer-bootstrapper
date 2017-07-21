<?php

namespace Graphite\Deployer\ProjectType;

class Laravel implements ProjectType
{
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
        // TODO: Implement getDeployTasks() method.
    }
}
