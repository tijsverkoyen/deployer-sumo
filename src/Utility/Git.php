<?php

namespace TijsVerkoyen\DeployerSumo\Utility;

use Deployer;

class Git
{
    public function getCurrentHash(): string
    {
        Deployer\cd('{{deploy_path}}/.dep/repo');
        return trim(Deployer\run('git log -n 1 --format="%H"'));
    }
}
