<?php

namespace Deployer;

use Deployer\Utility\Httpie;

// where Chrome is installed, {{chrome_path}}/current/chrome is the binary
set('chrome_path', '{{deploy_path}}/shared/chrome');

desc('Download the latest stable Chrome for Testing into {{chrome_path}}');
task('sumo:chrome:install', function () {
    $platforms = [
        'Linux x86_64' => 'linux64',
        'Linux aarch64' => 'linux-arm64',
        'Linux arm64' => 'linux-arm64',
    ];
    $system = run('uname -sm');
    if (!isset($platforms[$system])) {
        throw error(sprintf('Only Linux x86_64 and arm64 are supported, not %1$s.', $system));
    }
    $platform = $platforms[$system];

    $stable = Httpie::get(
        'https://googlechromelabs.github.io/chrome-for-testing/last-known-good-versions-with-downloads.json'
    )->sendJson()['channels']['Stable'];
    $url = null;
    foreach ($stable['downloads']['chrome'] as $download) {
        if ($download['platform'] === $platform) {
            $url = $download['url'];
        }
    }
    if ($url === null) {
        throw error(sprintf('No Chrome for Testing download found for %1$s.', $platform));
    }

    $version = $stable['version'] . '-' . $platform;
    $versionPath = parse('{{chrome_path}}/' . $version);

    if (test(sprintf('[ -x %1$s ]', escapeshellarg($versionPath . '/chrome-' . $platform . '/chrome')))) {
        writeln(sprintf('Chrome %1$s is already installed.', $version));
    } else {
        writeln(sprintf('Downloading Chrome %1$s.', $version));
        run('mkdir -p {{chrome_path}}');
        run(sprintf('curl -sSfo %1$s %2$s', escapeshellarg($versionPath . '.zip'), escapeshellarg($url)));
        run(sprintf(
            'rm -rf %1$s && unzip -q %2$s -d %1$s && rm %2$s',
            escapeshellarg($versionPath),
            escapeshellarg($versionPath . '.zip')
        ));
    }

    $binary = $versionPath . '/chrome-' . $platform . '/chrome';
    try {
        writeln(run(escapeshellarg($binary) . ' --version'));
    } catch (\Throwable $e) {
        writeln('<comment>Chrome does not start, missing libraries:</comment>');
        writeln(run(sprintf('ldd %1$s | grep "not found" || true', escapeshellarg($binary))));
        writeln(sprintf(
            '<comment>The packages Chrome needs are listed in %1$s/deb.deps (Debian/Ubuntu) and rpm.deps.</comment>',
            dirname($binary)
        ));

        throw $e;
    }

    run(sprintf('ln -sfn %1$s {{chrome_path}}/current', escapeshellarg($version . '/chrome-' . $platform)));
    writeln(sprintf('Chrome binary: <info>%1$s</info>', parse('{{chrome_path}}/current/chrome')));
});
