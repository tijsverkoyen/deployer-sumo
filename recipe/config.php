<?php

namespace Deployer;

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

        // If there is no local .env.local file, make it
        if (!testLocally(sprintf('[ -f %1$s ]', $localConfigFile))) {
            runLocally('touch ' . $localConfigFile);
        }

        download($hostConfigFile, $localConfigFile);
    }
);

desc('Alter the config file for local use.');
task(
    'sumo:config:alter',
    function () {
        $localConfigFile = '.env.local';

        // If there is no local .env.local file, make it
        if (!testLocally(sprintf('[ -f %1$s ]', $localConfigFile))) {
            invoke('sumo:config:get');
        }

        //tweak the file
        /*
         * TODO:
         * APP_ENV=prod -> APP_ENV=dev
         * SENTRY_DSN -> leegmaken?
         * DATABASE_URL -> user/host/password/dbname aanpassen
         * 
         * Zeker ook lijn printen om zelf de .env.local nog na te kijken
         * custom parameters gaan we nooit kunnen opvangen
         */
    }
);
