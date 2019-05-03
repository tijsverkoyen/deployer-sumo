<?php

namespace Deployer;

use Symfony\Component\Console\Input\InputArgument;

argument('command-to-run', InputArgument::OPTIONAL, ' Command to run');
desc('Run a symfony console command');
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
