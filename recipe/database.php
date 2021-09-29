<?php

namespace Deployer;

use TijsVerkoyen\DeployerSumo\Utility\Database;
use TijsVerkoyen\DeployerSumo\Utility\Configuration;

$databaseUtility = new Database();

desc('Create the database if it does not exists yet');
task(
    'sumo:db:create',
    function () use ($databaseUtility) {
        writeln(
            run('create_db ' . $databaseUtility->getName())
        );
    }
)->select('stage=staging');

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
        $remoteDatabaseUrl = parse_url(
            Configuration::fromRemote()->get('DATABASE_URL')
        );
        $localDatbaseUrl = parse_url(
            Configuration::fromLocal()->get('DATABASE_URL')
        );

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
                $databaseUtility->getConnectionOptions($localDatbaseUrl),
                $databaseUtility->getNameFromConnectionOptions($localDatbaseUrl)
            )
        );
        runLocally('rm ./db_download.tmp.sql');
    }
);

desc('Replace the remote database with the local database');
task(
    'sumo:db:put',
    function () use ($databaseUtility) {
        $remoteDatabaseUrl = parse_url(
            Configuration::fromRemote()->get('DATABASE_URL')
        );
        $localDatbaseUrl = parse_url(
            Configuration::fromLocal()->get('DATABASE_URL')
        );

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
                'mysqldump --column-statistics=0 --lock-tables=false --set-charset %1$s %2$s > ./db_upload.tmp.sql',
                $databaseUtility->getConnectionOptions($localDatbaseUrl),
                $databaseUtility->getNameFromConnectionOptions($localDatbaseUrl)
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
