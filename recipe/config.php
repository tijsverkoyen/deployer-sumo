<?php

namespace Deployer;

use TijsVerkoyen\DeployerSumo\Utility\Database;

desc('Get the required config files from the host.');
task(
    'sumo:config:get',
    function () {
        $hostConfigFile = '{{deploy_path}}/shared/.env.local';
        $localConfigFile = '.env.local';

        // If there is no .env.local file on staging, stop
        if (!test(sprintf('[ -f %1$s ]', $hostConfigFile))) {
            return;
        }

        // Check if there is a local .env file
        if (testLocally(sprintf('[ -f %1$s ]', $localConfigFile))) {
            if (askConfirmation('Found an existing .env.local file. Should we overwrite it?')) {
                download($hostConfigFile, $localConfigFile);
            }
        } else {
            runLocally('touch ' . $localConfigFile);
            download($hostConfigFile, $localConfigFile);
        }
    }
);

desc('Alter the config file for local use.');
task(
    'sumo:config:alter',
    function () {
        $databaseUtility = new Database();
        $localConfigFile = '.env.local';
        $content = file_get_contents($localConfigFile);

        // Switch to dev mode
        $content = preg_replace('/APP_ENV=prod/', 'APP_ENV=dev', $content);

        // Empty out the Sentry DSN
        $content = preg_replace('/^.*SENTRY_DSN.*$/m', 'SENTRY_DSN=', $content);

        // Replace the database URL
        $newDatabaseUrl = 'DATABASE_URL="mysql://root:root@127.0.0.1:3306/%s"';
        $localDatabaseName = $databaseUtility->getName();
        $content = preg_replace('/^.*DATABASE_URL.*$/m', sprintf($newDatabaseUrl, $localDatabaseName), $content);

        file_put_contents($localConfigFile, $content);
    }
);
