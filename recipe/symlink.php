<?php

namespace Deployer;

use TijsVerkoyen\DeployerSumo\Utility\Path;

$pathUtility = new Path();

desc('Symlink the document root to the public folder');
task(
    'sumo:symlink:document-root',
    function () use ($pathUtility) {
        if (!get('document_root', false)) {
            return;
        }

        if (get('public_path') === null) {
            $publicPath = get('deploy_path') . '/current/public/';
        } else {
            $publicPath = get('public_path');
        }
        
        $currentSymlink = run(
            'if [ -L {{document_root}} ]; then readlink {{document_root}}; fi'
        );

        // already linked, so we can stop here
        if ($currentSymlink === $pathUtility->expandPath($publicPath)) {
            return;
        }

        // Show a warning when the document root exists. So we don't overwrite
        // existing stuff.
        if ($currentSymlink === '' && test('[ -e {{document_root}} ]')) {
            writeln(
                [
                    '<comment>Document root already exists</comment>',
                    '<comment>To link it, issue the following command:</comment>',
                    sprintf(
                        '<comment>ln -sf %1$s %2$s</comment>',
                        $publicPath,
                        get('document_root')
                    ),
                ]
            );
            return;
        }

        run(sprintf('mkdir -p %1$s', dirname(get('document_root'))));
        run('rm -f {{document_root}}');
        run(sprintf('{{bin/symlink}} %1$s {{document_root}}', $publicPath));
    }
);

// add it to the flow
after('deploy:symlink', 'sumo:symlink:document-root');
