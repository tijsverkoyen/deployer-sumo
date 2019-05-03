<?php

namespace Deployer;

use Symfony\Component\Console\Input\InputArgument;

argument('command-to-run', InputArgument::REQUIRED, ' Command to run');
task(
    'symfony:console',
    function () {
        run(
            sprintf(
                '{{bin/php}} {{current_path}}/bin/console %1$s',
                input()->getArgument('command-to-run')
            ),
            ['tty' => true]
        );
    }
);
