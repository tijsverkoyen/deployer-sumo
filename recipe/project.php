<?php

namespace Deployer;

desc('Get the project from the specified host.');
task(
    'sumo:project:get',
    function () {
        invoke('sumo:db:create-locale');
        invoke('sumo:db:get');
        invoke('sumo:config:alter');
        invoke('sumo:files:get');
        invoke('sumo:assets:fix-npm'); // niet nodig? nvm exec doet ook install?
        invoke('sumo:assets:build');
    }
);

// TODO: oudated?
before('deploy:symlink', 'sumo:assets:install');
