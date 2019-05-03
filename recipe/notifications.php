<?php

namespace Deployer;

use Deployer\Utility\Httpie;

require_once __DIR__ . '/../common.php';

desc('Notify our webhooks on a deploy');
task(
    'sumo:notifications:deploy',
    function () {
        Httpie::post('http://bot.sumo.sumoapp.be:3001/deploy/hook')
              ->body(
                  [
                      'local_username' => getenv('USER'),
                      'stage' => get('stage'),
                      'repo' => get('repository'),
                      'revision' => getCurrentGitHash(),
                  ]
              )
              ->send();
    }
);

// add it to the flow
after('deploy', 'sumo:notifications:deploy');
