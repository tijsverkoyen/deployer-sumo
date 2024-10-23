<?php

namespace Deployer;

desc('Run the build script which will build our needed assets.');
task(
    'sumo:assets:build',
    function () {
        if(testLocally('symfony')) {
            runLocally('symfony console sass:build');
            runLocally('symfony console asset-map:compile');
        } else {
            runLocally('php bin/console importmap:install --no-interaction');
            runLocally('php bin/console sass:build --no-interaction');
            runLocally('php bin/console asset-map:compile --no-interaction');
        }
    }
);

desc('Uploads the assets');
task(
    'sumo:assets:upload',
    function () {
        upload('public/assets', '{{release_path}}/public');
        upload('var/sass', '{{release_path}}/var');
    }
);

// Specify order during deploy
after('deploy:update_code', 'sumo:assets:build');
after('sumo:assets:build', 'sumo:assets:upload');
