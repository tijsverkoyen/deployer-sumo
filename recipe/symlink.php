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
        if ($currentSymlink === '' && folderExists(get('document_root'))) {
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
