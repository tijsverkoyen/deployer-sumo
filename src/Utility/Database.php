<?php

namespace TijsVerkoyen\DeployerSumo\Utility;

use Deployer;

final class Database
{
    public function getName(): string
    {
        return sprintf(
            '%1$s_%2$s',
            mb_substr(Deployer\get('client'), 0, 8),
            mb_substr(Deployer\get('project'), 0, 7)
        );
    }

    public function getNameFromConnectionOptions(array $config): string
    {
        if (!array_key_exists('path', $config)) {
            throw new \RuntimeException(
                'No database name found in connection string'
            );
        }

        return ltrim($config['path'], '/');
    }

    public function getConnectionOptions(array $config): string
    {
        $options = [];
        if (array_key_exists('host', $config)) {
            $options[] = '--host=' . $config['host'];
        }
        if (array_key_exists('user', $config)) {
            $options[] = '--user=' . $config['user'];
        }
        if (array_key_exists('pass', $config)) {
            $options[] = '--password=' . $config['pass'];
        }

        return implode(' ', $options);
    }
}
