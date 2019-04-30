<?php

namespace Deployer;

function expandPath($path): string
{
    return str_replace(
        '~/',
        run('echo $HOME') . '/',
        $path
    );
}

function folderExists($path): bool
{
    return run(
               sprintf(
                   'if [ -e %1$s ]; then echo "true"; fi',
                   $path
               )
           ) === 'true';
}
