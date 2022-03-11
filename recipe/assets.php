<?php

namespace Deployer;

desc('Install bundle\'s web assets under a public directory');
task(
    'sumo:assets:install',
    function () {
        $nvmPath = trim(shell_exec('echo $HOME/.nvm/nvm.sh'));

        if (!file_exists($nvmPath)) {
            writeln('Nvm not found on local system. Aborting');
            return;
        }

        $nvmRcFile = '.nvmrc';

        // If there is no .nvmrc file, stop
        if (!file_exists($nvmRcFile)) {
            writeln('No .nvmrc file found. Aborting.');
            return;
        }

        writeln(runLocally('. ' . $nvmPath . ' && nvm install'));
    }
);

desc('Run the build script which will build our needed assets.');
task(
    'sumo:assets:build',
    function () {
        $nvmPath = trim(shell_exec('echo $HOME/.nvm/nvm.sh'));

        if (file_exists($nvmPath)) {
            runLocally('. ' . $nvmPath . ' && nvm use && nvm exec npm run build');
        } else {
            runLocally('npm run build');
        }
    }
);

desc('Uploads the assets');
task(
    'sumo:assets:upload',
    function () {
        upload('/public/build', '{{release_path}}/public');
    }
);

// add it to the flow
before('deploy:symlink', 'sumo:assets:install');
