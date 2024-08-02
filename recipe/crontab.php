<?php

namespace Deployer;

desc('Symlink the crontab file');
task(
    'sumo:symlink:crontab',
    function () {
        // skip if there is no crontab file
        if (!test('[ -f {{release_or_current_path}}/.crontab ]')) {
            return;
        }

        // show warning if we can't detect the ~/.crontab folder
        if (!test('[ -d $HOME/.crontab ]')) {
            writeln(
                '<comment>No ~/.crontab folder found. You are probably not on a Cloudstar environment.</comment>'
            );
            writeln(
                '<comment>You will need to configure the cronjob(s) below manually.</comment>'
            );
            writeln('');
            writeln(run('cat {{release_or_current_path}}/.crontab'));

            return;
        }

        if (
            test('[ -f $HOME/.crontab/{{remote_user}}.crontab ]')
            && !test('[ -L $HOME/.crontab/{{remote_user}}.crontab ]')
        ) {
            writeln(sprintf(
                '<comment>It seems there is already a crontab file(%1$s) which is not a symlink.</comment>',
                '~/.crontab/{{remote_user}}.crontab'
            ));
            writeln(
                '<comment>Therefor we will not overwrite it. You can either remove this file and ' .
                'rerun this task</comment>',
            );
            writeln(
                '<comment>or you need to configure the cronjob(s) below manually.</comment>',
            );
            writeln('');
            writeln(run('cat {{release_or_current_path}}/.crontab'));

            return;
        }

        run(sprintf(
            '{{bin/symlink}} %1$s %2$s',
            '{{release_or_current_path}}/.crontab',
            '$HOME/.crontab/{{remote_user}}.crontab'
        ));
    }
)->select('stage=production');

// Specify order during deploy
after('deploy:symlink', 'sumo:symlink:crontab');
