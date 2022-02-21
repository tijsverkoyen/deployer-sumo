<?php

namespace Deployer;

desc('Create the SSL certificate for the staging url');
task(
    'sumo:ssl:create',
    function () {
        $command = sprintf(
            'create_ssl %1$s.%2$s.%3$s',
            get('project'),
            get('client'),
            'php' . str_replace('.', '', get('php_version'))
        );

        writeln(
            run($command)
        );
    }
)->select('stage=staging');
