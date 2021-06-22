<?php

namespace Deployer;

desc('Install bundle\'s web assets under a public directory');
task(
    'sumo:assets:install',
    function () {
        run('{{bin/php}} {{bin/console}} assets:install {{console_options}}');
    }
);

// add it to the flow
after('deploy:symlink', 'sumo:assets:install');
