<?php

namespace TijsVerkoyen\DeployerSumo\Utility;

use Deployer;

class Path
{
    public function expandPath($path): string
    {
        return str_replace(
            '~/',
            Deployer\run('echo $HOME') . '/',
            $path
        );
    }
}
