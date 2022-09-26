<?php

namespace Deployer;

desc('Replace the local files with the remote files');
task(
    'sumo:files:get',
    function () {
        $sharedDirectories = get('shared_dirs');
        if (!is_array($sharedDirectories) || empty($sharedDirectories)) {
            return;
        }

        foreach ($sharedDirectories as $directory) {
            $path = '{{deploy_path}}/shared/' . $directory;

            if (test(sprintf('[ -d %1$s ]', $path))) {
                // make sure path exists locally
                runLocally('mkdir -p ' . $directory);
                download($path, $directory. '/../');
            }
        }
    }
);

desc('Replace the remote files with the local files');
task(
    'sumo:files:put',
    function () {
        // ask for confirmation
        if (!askConfirmation('Are you sure? This will overwrite files on production!')) {
            return;
        }

        $sharedDirectories = get('shared_dirs');
        if (!is_array($sharedDirectories) || empty($sharedDirectories)) {
            return;
        }

        // remove some system dirs
        $directoriesToIgnore = [
            'var/log',      // this directory may contain useful information
            'var/sessions', // this directory may contain active sessions
        ];
        $sharedDirectories = array_values(array_filter(
            $sharedDirectories,
            function ($element) use ($directoriesToIgnore) {
                return !in_array($element, $directoriesToIgnore);
            }
        ));

        foreach ($sharedDirectories as $directory) {
            upload('./' . $directory, '{{deploy_path}}/shared/' . $directory . '/../');
        }
    }
);
