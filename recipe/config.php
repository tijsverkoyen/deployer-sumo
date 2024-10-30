<?php

namespace Deployer;

use TijsVerkoyen\DeployerSumo\Utility\Configuration;
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

desc('Upload the local .env.local file');
task(
    'sumo:config:put',
    function () {
        $hostConfigFile = '{{deploy_path}}/shared/.env.local';

        // Check if there is a .env.local file on staging
        if (test(sprintf('[ -f %1$s ]', $hostConfigFile))) {
            if (!askConfirmation('Found an existing .env.local file. Should we overwrite it?')) {
                return;
            }
        }

        $localConfigFile = '.env.local';
        $temporaryLocalConfigFile = '.env.local.tmp';

        $content = file_get_contents($localConfigFile);

        // Remove APP_ENV
        $content = preg_replace('/^.*APP_ENV.*\n/', '', $content);

        // Fix DATABASE_URL
        $config = Configuration::fromRemote();
        if ($config->has('DATABASE_URL')) {
            $databaseUrl = sprintf('DATABASE_URL="%1$s"', $config->get('DATABASE_URL'));
        } else {
            $databaseUtility = new Database();
            $output = trim(run('info_db ' . $databaseUtility->getName()));

            foreach (explode("\n", $output) as $line) {
                list($key, $value) = explode(':', $line);

                $databaseInfo[trim($key)] = trim($value);
            }

            $databaseUrl = sprintf(
                'DATABASE_URL="mysql://%2$s:%3$s@127.0.0.1:3306/%1$s"',
                $databaseInfo['database'],
                $databaseInfo['user'],
                $databaseInfo['pass'],
            );
        }
        $content = preg_replace('/^.*DATABASE_URL.*$/m', $databaseUrl, $content);

        file_put_contents($temporaryLocalConfigFile, $content);

        run('mkdir -p {{deploy_path}}/shared/');
        upload($temporaryLocalConfigFile, $hostConfigFile);
        runLocally('rm ' . $temporaryLocalConfigFile);
    }
)->select('stage=staging');

desc('Alter the config file for local use.');
task(
    'sumo:config:alter',
    function () {
        $databaseUtility = new Database();
        $localConfigFile = '.env.local';

        if (!testLocally(sprintf('[ -f %1$s ]', $localConfigFile))) {
            runLocally('touch ' . $localConfigFile);
        }

        $content = file_get_contents($localConfigFile);

        if ($content) {
            // Switch to dev mode
            $content = preg_replace('/APP_ENV=prod/', 'APP_ENV=dev', $content);

            // Empty out the Sentry DSN
            $content = preg_replace('/^.*SENTRY_DSN.*$/m', 'SENTRY_DSN=', $content);

            // Replace the database URL
            $newDatabaseUrl = 'DATABASE_URL="mysql://root:root@127.0.0.1:3306/%s"';
            $localDatabaseName = $databaseUtility->getName();
            $content = preg_replace('/^.*DATABASE_URL.*$/m', sprintf($newDatabaseUrl, $localDatabaseName), $content);
        } else {
            // Create file for dev mode with the default database
            $newDatabaseUrl = 'DATABASE_URL="mysql://root:root@127.0.0.1:3306/%s"';
            $localDatabaseName = $databaseUtility->getName();
            $content = 'APP_ENV=dev' . PHP_EOL . sprintf($newDatabaseUrl, $localDatabaseName) . PHP_EOL;
        }

        file_put_contents($localConfigFile, $content);
    }
);
