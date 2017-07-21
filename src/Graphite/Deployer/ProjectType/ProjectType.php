<?php

namespace Graphite\Deployer\ProjectType;

interface ProjectType
{
    public function setRecipeDirectory($recipeDirectory);

    public function getSharedFiles();

    public function getSharedDirectories();

    public function getWritableDirectories();

    public function getCopyDirectories();

    public function getDeployTasks();
}
