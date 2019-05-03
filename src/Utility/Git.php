<?php

namespace TijsVerkoyen\DeployerSumo\Utility;

use Deployer;

class Git
{
    public function getCurrentHash(): string
    {
        Deployer\cd('{{release_path}}');
        return trim(Deployer\run('git log -n 1 --format="%H"'));
    }
}
