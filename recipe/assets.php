<?php

namespace Deployer;

desc('Run the build script which will build our needed assets.');
task(
    'sumo:assets:fix-node-version',
    function () {
        if (!shell_exec('command -v volta')) {
            writeln('Volta not found on local system.');
        }

        if (isset(json_decode(file_get_contents('package.json') ?: '', true)['volta'])) {
            return;
        }

        warning('Switching to Volta is recommended.');
        writeln('Run `volta pin node@lts && rm .nvmrc` and commit');

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

        if (file_exists($nvmPath) && file_exists('.nvmrc')) {
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
        upload('public/build', '{{release_path}}/public');
    }
);

// Specify order during deploy
after('deploy:update_code', 'sumo:assets:build');
after('sumo:assets:build', 'sumo:assets:upload');