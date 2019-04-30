<?php

namespace Deployer;

require_once __DIR__ . '/../common.php';

desc('Symlink the document root to the public folder');
task(
    'sumo:symlink:document-root',
    function () {
        if (!get('document_root', false)) {
            return;
        }

        $publicPath = get('deploy_path') . '/current/public/';
        $currentSymlink = run(
            'if [ -L {{document_root}} ]; then readlink {{document_root}}; fi'
        );

        // already linked, so we can stop here
        if ($currentSymlink === expandPath($publicPath)) {
            return;
        }

        // Show a warning when the document root exists. So we don't overwrite
        // existing stuff.
        if (folderExists(get('document_root'))) {
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

        // create the parent folder and the symlink
        run(
            sprintf(
                'mkdir -p %1$s; ln -sf %2$s %3$s',
                dirname(get('document_root')),
                $publicPath,
                get('document_root')
            )
        );
    }
);

// add it to the flow
after('deploy:symlink', 'sumo:symlink:document-root');
