<?php

namespace Deployer;

use Deployer\Utility\Httpie;
use TijsVerkoyen\DeployerSumo\Utility\Git;

$gitUtility = new Git();

desc('Notify our webhooks on a deploy');
task(
    'sumo:notifications:deploy',
    function () use ($gitUtility) {
        Httpie::post('https://bot.sumo.sumoapp.be/deploy/hook')
            ->jsonBody(
                [
                    'local_username' => getenv('USER'),
                    'stage' => get('labels')['stage'] ?? null,
                    'repo' => get('repository'),
                    'revision' => $gitUtility->getCurrentHash(),
                ]
            )
            ->send();
    }
);

// add it to the flow
after('deploy', 'sumo:notifications:deploy');
