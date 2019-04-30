<?php

namespace Deployer;

require_once __DIR__ . '/../common.php';

desc('Enable a redirect page, all traffic will be redirected to this page.');
task(
    'sumo:redirect:enable',
    function () {
        if (!get('production_url', false)) {
            throw new \RuntimeException("Set a production url");
        }

        set('redirect_path', get('deploy_path') . '/redirect');

        run('mkdir -p {{redirect_path}}');
        run(
            'wget -qO {{redirect_path}}/index.php http://static.sumocoders.be/redirect/index.phps'
        );
        run(
            'wget -qO {{redirect_path}}/.htaccess http://static.sumocoders.be/redirect/htaccess'
        );
        run(
            'sed -i "s|<real-url>|{{production_url}}|" {{redirect_path}}/index.php'
        );

        run('rm {{document_root}}');
        run('{{bin/symlink}} {{redirect_path}} {{document_root}}');
    }
)->addAfter('cachetool:clear:opcache');
