<?php

namespace Deployer;

set('public_path', function () {
    if (test('[ -f {{release_path}}/public ]')) {
        return get('deploy_path') . '/current/public/';
    }

    return get('deploy_path') . '/current/';
});