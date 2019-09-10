<?php

namespace Deployer;

use Deployer\Utility\Httpie;

desc('Reset the opcache using a file strategy');
task('sumo:opcache:reset-file', function () {
    $opcacheResetScript = 'php-opcache_reset.php';
    $publicDirectory = '{{ release_path }}/';
    if (test('[ -d {{release_path}}/public ]')) {
        $publicDirectory = '{{ release_path }}/public';
    }
    $scriptPath =  $publicDirectory . '/' . $opcacheResetScript;

    run(
        'echo "<?php clearstatcache(true); if (function_exists(\'opcache_reset\')) { opcache_reset(); }" > ' .
        $scriptPath
    );

    $response = Httpie::get(get('production_url') . '/' . $opcacheResetScript)->send();
    if ($response === false) {
        writeln('<comment>Could not perform an opcache reset via file.</comment>');
    }

    run('rm ' . $scriptPath);
});
