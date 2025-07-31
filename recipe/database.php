<?php

namespace Deployer;

use TijsVerkoyen\DeployerSumo\Utility\Database;
use TijsVerkoyen\DeployerSumo\Utility\Configuration;

$databaseUtility = new Database();

desc('Create the staging database if it does not exists yet');
task(
    'sumo:db:create',
    function () use ($databaseUtility) {
        writeln(
            run('create_db ' . $databaseUtility->getName())
        );
    }
)->select('stage=staging');

desc('Create the local database if it does not exists yet');
task(
    'sumo:db:create-local',
    function () use ($databaseUtility) {
        $localDatabaseUrl = parse_url(
            Configuration::fromLocal()->get('DATABASE_URL')
        );

        runLocally(
            sprintf(
                'mysql %1$s -e "CREATE DATABASE IF NOT EXISTS %2$s;"',
                $databaseUtility->getConnectionOptions($localDatabaseUrl),
                $databaseUtility->getNameFromConnectionOptions($localDatabaseUrl)
            )
        );
    }
);

desc('Get info about the database');
task(
    'sumo:db:info',
    function () use ($databaseUtility) {
        writeln(
            run('info_db ' . $databaseUtility->getName())
        );
    }
)->select('stage=staging');

desc('Replace the local database with the remote database');
task(
    'sumo:db:get',
    function () use ($databaseUtility) {
        try {
            $local = runLocally("symfony console secrets:reveal DATABASE_URL --env dev");
            $remote = run("{{ bin/php }} {{ current_path }}/bin/console secrets:reveal DATABASE_URL");
        } catch (\Exception $exception) {
            $local = $remote = null;
        }

        if ($remote === null || $remote === '') {
            try {
                $remote = Configuration::fromRemote()->get('DATABASE_URL');
            } catch (\RuntimeException $exception) {
                warning('Database does not exist on remote.');

                return;
            }
        }

        if ($local === null || $local === '') {
            $local = Configuration::fromLocal()->get('DATABASE_URL');
        }

        $remoteDatabaseUrl = parse_url($remote);
        $localDatabaseUrl = parse_url($local);

        run(
            sprintf(
                'mysqldump --lock-tables=false --set-charset %1$s %2$s > {{deploy_path}}/db_download.tmp.sql',
                $databaseUtility->getConnectionOptions($remoteDatabaseUrl),
                $databaseUtility->getNameFromConnectionOptions($remoteDatabaseUrl)
            )
        );
        download(
            '{{deploy_path}}/db_download.tmp.sql',
            './db_download.tmp.sql'
        );
        run('rm {{deploy_path}}/db_download.tmp.sql');

        runLocally(
            sprintf(
                'mysql %1$s %2$s < ./db_download.tmp.sql',
                $databaseUtility->getConnectionOptions($localDatabaseUrl),
                $databaseUtility->getNameFromConnectionOptions($localDatabaseUrl)
            )
        );
        runLocally('rm ./db_download.tmp.sql');
    }
);

desc('Replace the remote database with the local database');
task(
    'sumo:db:put',
    function () use ($databaseUtility) {
        try {
            $local = runLocally("symfony console secrets:reveal DATABASE_URL --env dev");
            $remote = run("{{ bin/php }} {{ current_path }}/bin/console secrets:reveal DATABASE_URL");
        } catch (\Exception $exception) {
            $local = $remote = null;
        }

        if ($remote === null || $remote === '') {
            $remote = Configuration::fromRemote()->get('DATABASE_URL');
        }

        if ($local === null || $local === '') {
            $local = Configuration::fromLocal()->get('DATABASE_URL');
        }

        $remoteDatabaseUrl = parse_url($remote);
        $localDatabaseUrl = parse_url($local);

        // create a backup
        // @todo make separate backup dir
        run(
            sprintf(
                'mysqldump --lock-tables=false --set-charset %1$s %2$s > {{deploy_path}}/backup_%3$s.sql',
                $databaseUtility->getConnectionOptions($remoteDatabaseUrl),
                $databaseUtility->getNameFromConnectionOptions($remoteDatabaseUrl),
                date('YmdHi')
            )
        );

        runLocally(
            sprintf(
                'mysqldump --lock-tables=false --set-charset %1$s %2$s > ./db_upload.tmp.sql',
                $databaseUtility->getConnectionOptions($localDatabaseUrl),
                $databaseUtility->getNameFromConnectionOptions($localDatabaseUrl)
            )
        );

        upload('./db_upload.tmp.sql', '{{deploy_path}}/db_upload.tmp.sql');
        runLocally('rm ./db_upload.tmp.sql');

        run(
            sprintf(
                'mysql %1$s %2$s < {{deploy_path}}/db_upload.tmp.sql',
                $databaseUtility->getConnectionOptions($remoteDatabaseUrl),
                $databaseUtility->getNameFromConnectionOptions($remoteDatabaseUrl)
            )
        );
        run('rm {{deploy_path}}/db_upload.tmp.sql');
    }
);
