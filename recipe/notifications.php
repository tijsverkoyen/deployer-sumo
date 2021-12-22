<?php

namespace Deployer;

use Deployer\Utility\Httpie;
use TijsVerkoyen\DeployerSumo\Utility\Git;

$gitUtility = new Git();

desc('Notify our webhooks on a deploy');
task(
    'sumo:notifications:deploy',
    function () use ($gitUtility) {
        Httpie::post('http://bot.sumo.sumoapp.be:3001/deploy/hook')
              ->jsonBody(
                  [
                      'local_username' => getenv('USER'),
                      'stage' => get('stage'),
                      'repo' => get('repository'),
                      'revision' => $gitUtility->getCurrentHash(),
                  ]
              )
              ->send();
    }
);

// add it to the flow
after('deploy', 'sumo:notifications:deploy');
